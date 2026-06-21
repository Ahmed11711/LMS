<?php

namespace App\Services\DomainService;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DomainService
{
    private string $serverIp;
    private string $adminEmail    = 'admin@darab.academy';
    private string $webUser       = 'hestiamail';
    private string $wildcardCert  = '/etc/letsencrypt/live/darab.academy-0001';
    private string $protectedBase = 'darab.academy';

    private array $protectedSubdomains = ['www', 'api', 'mail', 'admin'];

    // Max time (seconds) a single setup/cleanup operation is allowed to hold
    // the lock for a given domain. Generous because certbot + nginx reload
    // can legitimately take a while.
    private const LOCK_TIMEOUT_SECONDS = 120;

    public function __construct()
    {
        $this->serverIp = config('domain.server_ip');
    }

    // ============================================================
    // Public Entry Point
    // ============================================================

    public function setupDomain(string $domain): array
    {
        $domain = strtolower(trim($domain));

        if ($this->isProtectedDomain($domain)) {
            return $this->fail("This domain is protected and cannot be used.");
        }

        $lock = Cache::lock("domain-setup:{$domain}", self::LOCK_TIMEOUT_SECONDS);

        if (!$lock->get()) {
            return $this->fail("A setup operation is already in progress for {$domain}. Please wait a moment and try again.");
        }

        try {
            return $this->isInternalSubdomain($domain)
                ? $this->setupSubdomain($domain)
                : $this->setupExternalDomain($domain);
        } catch (\Exception $e) {
            Log::error("Domain setup failed for {$domain}: " . $e->getMessage());
            return $this->fail($e->getMessage());
        } finally {
            $lock->release();
        }
    }

    public function cleanupDomain(string $domain): void
    {
        $domain = strtolower(trim($domain));

        if (empty($domain) || $this->isProtectedDomain($domain)) {
            Log::info("Skipping cleanup for protected/empty domain: {$domain}");
            return;
        }

        $lock = Cache::lock("domain-setup:{$domain}", self::LOCK_TIMEOUT_SECONDS);

        if (!$lock->get()) {
            Log::warning("Could not acquire lock to cleanup {$domain} — a setup may be in progress.");
            return;
        }

        try {
            $this->isInternalSubdomain($domain)
                ? $this->cleanupSubdomain($domain)
                : $this->cleanupExternalDomain($domain);
        } catch (\Exception $e) {
            Log::error("Domain cleanup failed for {$domain}: " . $e->getMessage());
        } finally {
            $lock->release();
        }
    }

    // ============================================================
    // Domain Classification
    // ============================================================

    public function isProtectedDomain(string $domain): bool
    {
        $domain = strtolower(trim($domain));

        if ($domain === $this->protectedBase) {
            return true;
        }

        foreach ($this->protectedSubdomains as $sub) {
            if ($domain === "{$sub}.{$this->protectedBase}") {
                return true;
            }
        }

        return false;
    }

    private function isInternalSubdomain(string $domain): bool
    {
        return str_ends_with($domain, '.' . $this->protectedBase);
    }

    // ============================================================
    // Setup: Internal Subdomain
    // ============================================================

    private function setupSubdomain(string $domain): array
    {
        $config = $this->generateNginxConfig($domain, $this->wildcardCert);

        $written = $this->writeNginxConfig($domain, $config);
        if (!$written) {
            return $this->fail("Failed to write Nginx config for {$domain}");
        }

        return $this->reloadNginx();
    }

    // ============================================================
    // Setup: External Domain
    // ============================================================

    private function setupExternalDomain(string $domain): array
    {
        // 1. Validate DNS
        $validation = $this->isDomainValid($domain);
        if (!$validation['valid']) {
            return $this->fail($validation['message']);
        }

        // 2. Check if a real cert already exists (e.g. re-running setup)
        $certPath = $this->findCertPath($domain);

        if (!$certPath) {
            // 2a. Write a TEMPORARY config using our wildcard cert.
            //     This makes Nginx accept connections for this domain
            //     on port 80/443, so the HTTP-01 challenge can succeed.
            $tempConfig = $this->generateNginxConfig($domain, $this->wildcardCert);
            if (!$this->writeNginxConfig($domain, $tempConfig)) {
                return $this->fail("Failed to write temporary Nginx config for {$domain}");
            }

            $tempReload = $this->reloadNginx();
            if (!$tempReload['success']) {
                $this->deleteNginxConfig($domain);
                $this->reloadNginx();
                return $this->fail("Temporary Nginx config invalid for {$domain}: " . ($tempReload['message'] ?? ''));
            }

            // 2b. Now request the real certificate — port 80 is live for this domain.
            $this->remountWritable();
            $certResult = $this->generateSSL($domain);

            if (!$certResult['success']) {
                // Roll back: remove the temp config, nothing real was created.
                $this->deleteNginxConfig($domain);
                $this->reloadNginx();
                return $certResult;
            }

            $certPath = $certResult['certPath'];
        }

        // 3. Write the FINAL config pointing at the real certificate.
        $finalConfig = $this->generateNginxConfig($domain, $certPath);
        if (!$this->writeNginxConfig($domain, $finalConfig)) {
            // We have a real cert now but couldn't write the final config.
            // Don't delete the cert — it's valid and reusable on retry.
            return $this->fail("Failed to write final Nginx config for {$domain}");
        }

        Log::info("Nginx config written for external domain: {$domain}");

        // 4. Reload Nginx with the final config.
        $reload = $this->reloadNginx();

        if (!$reload['success']) {
            // Roll back the bad config, but keep the cert (it's still valid
            // and reusable on the next attempt). A scheduled cleanup job
            // is responsible for removing certs that stay unused too long.
            $this->deleteNginxConfig($domain);
            $this->reloadNginx();
        }

        return $reload;
    }

    // ============================================================
    // Cleanup
    // ============================================================

    private function cleanupSubdomain(string $domain): void
    {
        $this->deleteNginxConfig($domain);
        $this->safeExec("sudo nginx -t && sudo systemctl reload nginx");
        Log::info("Cleaned up internal subdomain: {$domain}");
    }

    private function cleanupExternalDomain(string $domain): void
    {
        $this->deleteCert($domain);
        $this->deleteNginxConfig($domain);
        $this->safeExec("sudo nginx -t && sudo systemctl reload nginx");
        Log::info("Cleaned up external domain: {$domain}");
    }

    private function deleteCert(string $domain): void
    {
        $suffixes = ['', '-0001', '-0002', '-0003'];

        foreach ($suffixes as $suffix) {
            $certName = $domain . $suffix;
            $certPath = "/etc/letsencrypt/live/{$certName}";

            if (file_exists($certPath)) {
                $safe = escapeshellarg($certName);
                $this->safeExec("sudo certbot delete --cert-name {$safe} --non-interactive");
                Log::info("Deleted SSL cert: {$certName}");
            }
        }
    }

    private function deleteNginxConfig(string $domain): void
    {
        $configPath = "/etc/nginx/sites-enabled/{$domain}";

        if (file_exists($configPath)) {
            $this->safeExec("sudo rm -f " . escapeshellarg($configPath));
            Log::info("Deleted Nginx config: {$domain}");
        }
    }

    // ============================================================
    // SSL
    // ============================================================

    private function generateSSL(string $domain): array
    {
        $safeDomain = escapeshellarg($domain);
        $safeEmail  = escapeshellarg($this->adminEmail);

        $output = $this->safeExec(
            "sudo certbot certonly --webroot -w /var/www/LMS/public"
                . " -d {$safeDomain} --non-interactive --agree-tos -m {$safeEmail}"
        );

        Log::info("Certbot output for {$domain}: " . $output);

        // Fix permissions
        $this->safeExec("sudo chown -R root:{$this->webUser} /etc/letsencrypt/live/{$domain}/");
        $this->safeExec("sudo chown -R root:{$this->webUser} /etc/letsencrypt/archive/{$domain}/");
        $this->safeExec("sudo chmod 750 /etc/letsencrypt/live/{$domain}/");
        $this->safeExec("sudo chmod 750 /etc/letsencrypt/archive/{$domain}/");

        $certPath = $this->findCertPath($domain);

        if (!$certPath) {
            return $this->fail("SSL generation failed for {$domain}", ['ssl_output' => $output]);
        }

        return ['success' => true, 'certPath' => $certPath];
    }

    private function findCertPath(string $domain): ?string
    {
        foreach (['', '-0001', '-0002', '-0003'] as $suffix) {
            $path = "/etc/letsencrypt/live/{$domain}{$suffix}";
            if (file_exists("{$path}/fullchain.pem")) {
                return $path;
            }
        }
        return null;
    }

    // ============================================================
    // Nginx
    // ============================================================

    // private function writeNginxConfig(string $domain, string $config): bool
    // {
    //     $dest = "/etc/nginx/sites-enabled/{$domain}";

    //     $attempts = 3;
    //     for ($i = 1; $i <= $attempts; $i++) {
    //         $result = @file_put_contents($dest, $config);

    //         if ($result !== false) {
    //             return true;
    //         }

    //         Log::warning("file_put_contents failed for {$dest} (attempt {$i}/{$attempts})");

    //         if ($i < $attempts) {
    //             $this->safeExec("sudo mount -o remount,rw /");
    //             usleep(500_000); // 0.5s
    //         }
    //     }

    //     Log::error("file_put_contents failed for {$dest} after {$attempts} attempts");
    //     return false;
    // }

    private function writeNginxConfig(string $domain, string $config): bool
    {
        $dest   = "/etc/nginx/sites-enabled/{$domain}";
        $result = file_put_contents($dest, $config);

        if ($result === false) {
            Log::error("file_put_contents failed for: {$dest} | error: " . json_encode(error_get_last()));
            return false;
        }

        return true;
    }
    private function reloadNginx(): array
    {
        $testOutput = $this->safeExec("sudo nginx -t");

        if ($this->outputHasError($testOutput)) {
            Log::error("Nginx config test failed: " . $testOutput);
            return $this->fail("Nginx config test failed: " . $testOutput);
        }

        $reloadOutput = $this->safeExec("sudo systemctl reload nginx");

        if ($this->outputHasError($reloadOutput)) {
            Log::error("Nginx reload failed: " . $reloadOutput);
            return $this->fail("Nginx reload failed: " . $reloadOutput);
        }

        return ['success' => true];
    }

    private function generateNginxConfig(string $domain, string $certPath): string
    {
        return <<<NGINX
server {
    listen 80;
    server_name {$domain};
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl;
    server_name {$domain};

    ssl_certificate {$certPath}/fullchain.pem;
    ssl_certificate_key {$certPath}/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;
    }
}
NGINX;
    }

    // ============================================================
    // Validation
    // ============================================================

    private function isDomainValid(string $domain): array
    {
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return ['valid' => false, 'message' => "Invalid domain format"];
        }

        // 1. Check for a CNAME record first (the path we recommend to customers).
        $cnameRecords = @dns_get_record($domain, DNS_CNAME);

        // 2. Check for a direct A record too (some customers may use this instead).
        $aRecords = @dns_get_record($domain, DNS_A);

        if (empty($cnameRecords) && empty($aRecords)) {
            return [
                'valid'   => false,
                'message' => "No DNS records found for {$domain}. Please add a CNAME record pointing to cname.darab.academy, or contact support if DNS was just changed (propagation can take up to 24-48 hours)."
            ];
        }

        // 3. Resolve the domain to its final IP address, following the CNAME chain.
        //    gethostbyname() follows CNAMEs automatically and returns the final A record IP.
        $resolvedIp = gethostbyname($domain);

        if ($resolvedIp === $domain) {
            // gethostbyname() returns the input unchanged when resolution fails entirely.
            return [
                'valid'   => false,
                'message' => "Could not resolve {$domain} to an IP address. DNS changes can take up to 24-48 hours to propagate."
            ];
        }

        // 4. Compare against our known server IP (configured once, not fetched per request).
        if ($resolvedIp !== $this->serverIp) {
            $via = !empty($cnameRecords) ? "CNAME ({$cnameRecords[0]['target']})" : "A record";
            return [
                'valid'   => false,
                'message' => "Domain {$domain} resolves via {$via} to {$resolvedIp}, but our server is {$this->serverIp}. Please check your DNS settings."
            ];
        }

        return ['valid' => true, 'message' => "Domain is valid"];
    }

    // ============================================================
    // Helpers
    // ============================================================

    private function remountWritable(): void
    {
        $this->safeExec("sudo mount -o remount,rw /");
        $this->safeExec("sudo mount -o remount,rw /etc/letsencrypt");
    }

    /**
     * Execute a shell command safely.
     * Returns output string (never null).
     *
     * NOTE: this does NOT sanitize the command itself — every caller is
     * responsible for escaping any interpolated value with
     * escapeshellarg() before it reaches this method.
     */
    private function safeExec(string $command): string
    {
        $output = shell_exec($command . " 2>&1");
        return $output ?? '';
    }

    private function outputHasError(string $output): bool
    {
        return str_contains($output, 'failed') || str_contains($output, 'error');
    }

    private function fail(string $message, array $extra = []): array
    {
        return array_merge(['success' => false, 'message' => $message], $extra);
    }
}
