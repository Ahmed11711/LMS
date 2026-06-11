<?php

namespace App\Services\DomainService;

use Illuminate\Support\Facades\Log;

class DomainService
{
    private string $adminEmail    = 'admin@darab.academy';
    private string $webUser       = 'hestiamail';
    private string $wildcardCert  = '/etc/letsencrypt/live/darab.academy-0001';
    private string $protectedBase = 'darab.academy';

    private array $protectedSubdomains = ['www', 'api', 'mail', 'admin'];

    // ============================================================
    // Public Entry Point
    // ============================================================

    public function setupDomain(string $domain): array
    {
        $domain = strtolower(trim($domain));

        try {
            if ($this->isProtectedDomain($domain)) {
                return $this->fail("This domain is protected and cannot be used.");
            }

            return $this->isInternalSubdomain($domain)
                ? $this->setupSubdomain($domain)
                : $this->setupExternalDomain($domain);
        } catch (\Exception $e) {
            Log::error("Domain setup failed for {$domain}: " . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    public function cleanupDomain(string $domain): void
    {
        $domain = strtolower(trim($domain));

        if (empty($domain) || $this->isProtectedDomain($domain)) {
            Log::info("Skipping cleanup for protected/empty domain: {$domain}");
            return;
        }

        $this->isInternalSubdomain($domain)
            ? $this->cleanupSubdomain($domain)
            : $this->cleanupExternalDomain($domain);
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

        // 2. Find or generate SSL cert
        $certPath = $this->findCertPath($domain);

        if (!$certPath) {
            $this->remountWritable();
            $certResult = $this->generateSSL($domain);
            if (!$certResult['success']) {
                return $certResult;
            }
            $certPath = $certResult['certPath'];
        }

        // 3. Write Nginx config
        $config  = $this->generateNginxConfig($domain, $certPath);
        $written = $this->writeNginxConfig($domain, $config);

        if (!$written) {
            // Rollback cert if we just generated it
            $this->deleteCert($domain);
            return $this->fail("Failed to write Nginx config for {$domain}");
        }

        Log::info("Nginx config written for external domain: {$domain}");

        // 4. Reload Nginx
        $reload = $this->reloadNginx();

        if (!$reload['success']) {
            // Rollback: remove bad config
            $this->deleteNginxConfig($domain);
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

    private function writeNginxConfig(string $domain, string $config): bool
    {
        $dest   = "/etc/nginx/sites-enabled/{$domain}";
        $result = file_put_contents($dest, $config);

        if ($result === false) {
            Log::error("file_put_contents failed for: {$dest}");
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

        $domainIp = gethostbyname($domain);
        if ($domainIp === $domain) {
            return [
                'valid'   => false,
                'message' => "Please make sure {$domain} is pointing to our server IP. DNS changes can take up to 24-48 hours."
            ];
        }

        $serverIp = trim($this->safeExec("curl -4 -s ifconfig.me"));

        if ($domainIp !== $serverIp) {
            return [
                'valid'   => false,
                'message' => "Domain {$domain} does not point to this server. Got: {$domainIp}, Expected: {$serverIp}"
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
