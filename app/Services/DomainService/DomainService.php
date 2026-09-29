<?php

namespace App\Services\DomainService;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DomainService
{
    private string $serverIp;
    private string $adminEmail    = 'admin@darab.academy';
    private string $wildcardCert  = '/etc/letsencrypt/live/darab.academy-0001';
    private string $protectedBase = 'darab.academy';
    private string $pendingDir    = '/tmp/nginx-pending';

    /** Subdomains of the base domain that customers can never take. */
    private const RESERVED_SUBDOMAINS = [
        'www',
        'api',
        'mail',
        'admin',
        'cname',
        'hestia',
        'webmail',
        'ftp',
        'ns1',
        'ns2',
        'smtp',
        'imap',
        'pop',
        'pop3',
        'staging',
        'test',
        'dev',
        'app',
        'dashboard',
        'cpanel',
        'panel',
        'support',
        'status',
        'cdn',
    ];

    private const CERT_SUFFIXES = ['', '-0001', '-0002', '-0003'];

    // Worst case for a full external setup: ~70s (temp conf) + 120s (certbot) + 70s (final conf).
    // The lock must outlive that, otherwise a second request can slip in mid-operation.
    private const LOCK_TIMEOUT_SECONDS = 400;

    private const CRON_WAIT_SECONDS = 70;
    private const SSL_WAIT_SECONDS  = 120;

    public function __construct()
    {
        $this->serverIp = config('domain.server_ip');
    }

    // ============================================================
    // Public Entry Points
    // ============================================================

    public function setupDomain(string $domain): array
    {
        $domain = strtolower(trim($domain));

        if (!$this->isValidFormat($domain)) {
            return $this->fail("Invalid domain format.");
        }

        if ($this->isProtectedDomain($domain)) {
            return $this->fail("This domain is protected and cannot be used.");
        }

        $lock = Cache::store('file')->lock("domain-setup:{$domain}", self::LOCK_TIMEOUT_SECONDS);
        if (!$lock->get()) {
            return $this->fail("A setup operation is already in progress for {$domain}.");
        }

        try {
            return $this->isInternalSubdomain($domain)
                ? $this->setupSubdomain($domain)
                : $this->setupExternalDomain($domain);
        } catch (\Throwable $e) {
            Log::error("Domain setup failed for {$domain}: " . $e->getMessage());
            return $this->fail("Unexpected error while setting up {$domain}.");
        } finally {
            $lock->release();
        }
    }

    public function cleanupDomain(string $domain): void
    {
        $domain = strtolower(trim($domain));

        if ($domain === '' || !$this->isValidFormat($domain) || $this->isProtectedDomain($domain)) {
            Log::info("Skipping cleanup for invalid/protected/empty domain: {$domain}");
            return;
        }

        $lock = Cache::store('file')->lock("domain-setup:{$domain}", self::LOCK_TIMEOUT_SECONDS);
        if (!$lock->get()) {
            Log::warning("Could not acquire lock to cleanup {$domain} — a setup may be in progress.");
            return;
        }

        try {
            $this->isInternalSubdomain($domain)
                ? $this->cleanupSubdomain($domain)
                : $this->cleanupExternalDomain($domain);
        } catch (\Throwable $e) {
            Log::error("Domain cleanup failed for {$domain}: " . $e->getMessage());
        } finally {
            $lock->release();
        }
    }

    // ============================================================
    // Classification & Format Validation
    // ============================================================

    public function isProtectedDomain(string $domain): bool
    {
        $domain = strtolower(trim($domain));

        if ($domain === $this->protectedBase) {
            return true;
        }

        foreach (self::RESERVED_SUBDOMAINS as $sub) {
            if ($domain === "{$sub}.{$this->protectedBase}") {
                return true;
            }
        }

        return false;
    }

    /**
     * Strict whitelist. Everything that ends up in a file path, a shell
     * command or an nginx `server_name` must pass this first.
     */
    public function isValidFormat(string $domain): bool
    {
        if ($domain === '' || strlen($domain) > 253) {
            return false;
        }

        $label = '[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?';

        if ($this->isInternalSubdomain($domain)) {
            // Exactly one label: the wildcard cert only covers one level.
            return preg_match('/^' . $label . '\.' . preg_quote($this->protectedBase, '/') . '$/D', $domain) === 1;
        }

        $tld = '(?:[a-z]{2,63}|xn--[a-z0-9-]{1,59})';
        return preg_match('/^(?:' . $label . '\.)+' . $tld . '$/D', $domain) === 1;
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

        if (!$this->writeNginxConfig($domain, $config)) {
            return $this->fail("Failed to write Nginx config for {$domain}");
        }

        return ['success' => true];
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

        // 2. Reuse an existing cert if there is one (retry / re-run).
        $certPath = $this->findCertPath($domain);

        if (!$certPath) {
            // 2a. Temporary config on the wildcard cert so port 80 answers
            //     for this domain and the HTTP-01 challenge can succeed.
            $tempConfig = $this->generateNginxConfig($domain, $this->wildcardCert);
            if (!$this->writeNginxConfig($domain, $tempConfig)) {
                return $this->fail("Failed to write temporary Nginx config for {$domain}");
            }

            // 2b. Request the real certificate.
            $this->remountWritable();
            $certResult = $this->generateSSL($domain);

            if (!$certResult['success']) {
                $this->deleteNginxConfig($domain); // roll back the temp config
                return $certResult;
            }

            $certPath = $certResult['certPath'];
        }

        // 3. Final config with the real certificate.
        $finalConfig = $this->generateNginxConfig($domain, $certPath);
        if (!$this->writeNginxConfig($domain, $finalConfig)) {
            // Keep the cert (valid, reusable on retry) but remove the temp config,
            // otherwise visitors would get a certificate-mismatch warning.
            $this->deleteNginxConfig($domain);
            return $this->fail("Failed to write final Nginx config for {$domain}");
        }

        Log::info("Nginx config written for external domain: {$domain}");

        return ['success' => true];
    }

    // ============================================================
    // Cleanup
    // ============================================================

    private function cleanupSubdomain(string $domain): void
    {
        $this->deleteNginxConfig($domain, true);
        Log::info("Cleaned up internal subdomain: {$domain}");
    }

    private function cleanupExternalDomain(string $domain): void
    {
        // Order matters: config first (wait until the cron really removed it),
        // reload nginx, and only then delete the cert. Deleting the cert while
        // the config still references it makes `nginx -t` fail.
        if (!$this->deleteNginxConfig($domain, true)) {
            Log::error("Nginx config for {$domain} was not removed in time; keeping the cert.");
            return;
        }

        $this->safeExec("sudo nginx -t && sudo systemctl reload nginx");
        $this->deleteCert($domain);

        Log::info("Cleaned up external domain: {$domain}");
    }

    private function deleteCert(string $domain): void
    {
        foreach (self::CERT_SUFFIXES as $suffix) {
            $certName = $domain . $suffix;

            if (file_exists("/etc/letsencrypt/live/{$certName}")) {
                $safe = escapeshellarg($certName);
                $this->safeExec("sudo certbot delete --cert-name {$safe} --non-interactive");
                Log::info("Deleted SSL cert: {$certName}");
            }
        }
    }

    /**
     * Queue the deletion for the cron job. With $wait = true, blocks until the
     * file is really gone. Returns false if it is still there after the timeout.
     */
    private function deleteNginxConfig(string $domain, bool $wait = false): bool
    {
        $configPath = "/etc/nginx/sites-enabled/{$domain}";

        if (!file_exists($configPath)) {
            return true;
        }

        $this->ensurePendingDir();
        file_put_contents("{$this->pendingDir}/{$domain}.delete", "");
        Log::info("Queued Nginx config deletion for: {$domain}");

        if (!$wait) {
            return true;
        }

        $start = time();
        while (time() - $start < self::CRON_WAIT_SECONDS) {
            clearstatcache(true, $configPath);
            if (!file_exists($configPath)) {
                return true;
            }
            sleep(2);
        }

        return false;
    }

    // ============================================================
    // SSL
    // ============================================================

    private function generateSSL(string $domain): array
    {
        $this->ensurePendingDir();

        $pending    = "{$this->pendingDir}/{$domain}.ssl";
        $resultFile = "{$this->pendingDir}/{$domain}.ssl.result";
        $logFile    = "{$this->pendingDir}/{$domain}.ssl.log";

        @unlink($resultFile);
        @unlink($logFile);

        file_put_contents($pending, $this->adminEmail);
        Log::info("Queued SSL request for: {$domain}");

        $start = time();

        while (time() - $start < self::SSL_WAIT_SECONDS) {
            if (file_exists($resultFile)) {
                $result = trim(file_get_contents($resultFile));
                $log    = file_exists($logFile) ? file_get_contents($logFile) : '';
                @unlink($resultFile);
                @unlink($logFile);

                Log::info("Certbot output for {$domain}: " . $log);

                if ($result === 'success') {
                    $certPath = $this->findCertPath($domain);
                    if ($certPath) {
                        return ['success' => true, 'certPath' => $certPath];
                    }
                }

                // Certbot output stays in the log, never returned to the customer.
                return $this->fail("SSL certificate could not be issued for {$domain}. Please check your DNS settings and try again.");
            }
            sleep(2);
        }

        @unlink($pending);
        Log::error("Timed out waiting for SSL generation for: {$domain}");
        return $this->fail("SSL generation timed out for {$domain}. Please try again later.");
    }

    private function findCertPath(string $domain): ?string
    {
        foreach (self::CERT_SUFFIXES as $suffix) {
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

    private function ensurePendingDir(): void
    {
        if (!is_dir($this->pendingDir)) {
            mkdir($this->pendingDir, 0775, true);
        }
    }

    /**
     * Drops the config in the pending dir and waits for the
     * nginx-domain-sync.sh cron job to validate + deploy + reload it.
     */
    private function writeNginxConfig(string $domain, string $config): bool
    {
        $this->ensurePendingDir();

        $pending    = "{$this->pendingDir}/{$domain}.conf";
        $resultFile = "{$this->pendingDir}/{$domain}.nginx.result";

        @unlink($resultFile);

        if (file_put_contents($pending, $config) === false) {
            Log::error("Failed to write pending config for: {$domain} | error: " . json_encode(error_get_last()));
            return false;
        }

        $start = time();

        while (time() - $start < self::CRON_WAIT_SECONDS) {
            if (file_exists($resultFile)) {
                $resultContent = trim(file_get_contents($resultFile));
                @unlink($resultFile);

                if ($resultContent === 'success') {
                    Log::info("Nginx config deployed for: {$domain}");
                    return true;
                }

                Log::error("Nginx config failed for: {$domain} | " . $resultContent);
                return false;
            }
            sleep(2);
        }

        @unlink($pending);
        Log::error("Timed out waiting for nginx config deployment for: {$domain}");
        return false;
    }

    private function generateNginxConfig(string $domain, string $certPath): string
    {
        return <<<NGINX
server {
    listen 80;
    server_name {$domain};

    location /.well-known/acme-challenge/ {
        root /var/www/LMS/public;
        allow all;
    }

    location / {
        return 301 https://\$host\$request_uri;
    }
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
    // DNS Validation
    // ============================================================

    private function isDomainValid(string $domain): array
    {
        $cnameRecords = @dns_get_record($domain, DNS_CNAME) ?: [];
        $aRecords     = @dns_get_record($domain, DNS_A) ?: [];

        if (empty($cnameRecords) && empty($aRecords)) {
            return [
                'valid'   => false,
                'message' => "No DNS records found for {$domain}. Please add a CNAME record pointing to cname.darab.academy. If you just changed DNS, propagation can take up to 24-48 hours.",
            ];
        }

        // CNAME path (the one we recommend to customers).
        if (!empty($cnameRecords)) {
            $target = rtrim(strtolower($cnameRecords[0]['target'] ?? ''), '.');

            if ($target !== 'cname.darab.academy') {
                return [
                    'valid'   => false,
                    'message' => "Domain {$domain} has a CNAME pointing to {$target}, but it should point to cname.darab.academy.",
                ];
            }

            return ['valid' => true, 'message' => "Domain is valid"];
        }

        // Direct A record path: at least one A record must be our server.
        $ips = array_column($aRecords, 'ip');

        if (!in_array($this->serverIp, $ips, true)) {
            return [
                'valid'   => false,
                'message' => "Domain {$domain} resolves to " . implode(', ', $ips) . ", but our server is {$this->serverIp}. Please check your DNS settings.",
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
     * NOTE: does NOT sanitize the command. Every interpolated value must
     * already be passed through escapeshellarg() by the caller.
     */
    private function safeExec(string $command): string
    {
        return shell_exec($command . " 2>&1") ?? '';
    }

    private function fail(string $message, array $extra = []): array
    {
        return array_merge(['success' => false, 'message' => $message], $extra);
    }
}
