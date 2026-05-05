<?php

namespace App\Services\DomainService;

use Illuminate\Support\Facades\Log;

class DomainService
{
    private string $adminEmail    = 'admin@darab.academy';
    private string $webUser       = 'hestiamail';
    private string $wildcardCert  = '/etc/letsencrypt/live/darab.academy-0001';
    private string $protectedBase = 'darab.academy';

    public function setupDomain(string $domain): array
    {
        try {
            // 🛡️ منع الدومين الأساسي
            if ($this->isProtectedDomain($domain)) {
                return [
                    'success' => false,
                    'message' => "This domain is protected and cannot be used."
                ];
            }

            // Subdomain داخلي - استخدم wildcard cert بدون SSL
            if ($this->isInternalSubdomain($domain)) {
                return $this->setupSubdomain($domain);
            }

            // External domain - validate + SSL + Nginx
            return $this->setupExternalDomain($domain);
        } catch (\Exception $e) {
            Log::error("Domain setup failed for {$domain}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // ============================================================
    // Private Methods
    // ============================================================

    private function isProtectedDomain(string $domain): bool
    {
        $domain = strtolower(trim($domain));

        // منع الدومين الأساسي نفسه
        if ($domain === $this->protectedBase) {
            return true;
        }

        // منع www.darab.academy و api.darab.academy وأي subdomain محمي
        $protectedSubdomains = [
            'www.'   . $this->protectedBase,
            'api.'   . $this->protectedBase,
            'mail.'  . $this->protectedBase,
            'admin.' . $this->protectedBase,
        ];

        if (in_array($domain, $protectedSubdomains)) {
            return true;
        }

        return false;
    }

    private function isInternalSubdomain(string $domain): bool
    {
        return str_ends_with($domain, '.' . $this->protectedBase);
    }

    private function setupSubdomain(string $domain): array
    {
        $config      = $this->generateNginxConfig($domain, $this->wildcardCert);
        $writeResult = file_put_contents("/etc/nginx/sites-enabled/{$domain}", $config);

        if ($writeResult === false) {
            Log::error("Failed to write Nginx config for subdomain: {$domain}");
            return [
                'success' => false,
                'message' => "Failed to write Nginx config for {$domain}"
            ];
        }

        Log::info("Nginx config written for subdomain: {$domain} using wildcard cert");

        return $this->reloadNginx();
    }

    private function setupExternalDomain(string $domain): array
    {
        // 1. Validate domain
        $validation = $this->isDomainValid($domain);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        // 2. Find existing certificate
        $certPath = $this->findCertPath($domain);

        // 3. Generate new SSL if not found
        if (!$certPath) {
            $certResult = $this->generateSSL($domain);
            if (!$certResult['success']) {
                return $certResult;
            }
            $certPath = $certResult['certPath'];
        }

        // 4. Write Nginx config
        $config      = $this->generateNginxConfig($domain, $certPath);
        $writeResult = file_put_contents("/etc/nginx/sites-enabled/{$domain}", $config);

        if ($writeResult === false) {
            Log::error("Failed to write Nginx config for {$domain}");
            return [
                'success' => false,
                'message' => "Failed to write Nginx config for {$domain}"
            ];
        }

        Log::info("Nginx config written for external domain: {$domain} at {$certPath}");

        // 5. Reload Nginx
        return $this->reloadNginx();
    }

    private function isDomainValid(string $domain): array
    {
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return [
                'valid'   => false,
                'message' => "Invalid domain format"
            ];
        }

        $domainIp = gethostbyname($domain);
        if ($domainIp === $domain) {
            return [
                'valid'   => false,
                'message' => "Please make sure that the domain {$domain} is pointing to our server's IP address. If you have recently updated your DNS settings, please wait up to 24-48 hours for the changes to take effect."
            ];
        }

        $serverIp = trim(shell_exec("curl -4 -s ifconfig.me 2>&1"));

        if ($domainIp !== $serverIp) {
            return [
                'valid'   => false,
                'message' => "Domain {$domain} does not point to this server. Domain IP: {$domainIp}, Server IP: {$serverIp}"
            ];
        }

        return [
            'valid'   => true,
            'message' => "Domain is valid"
        ];
    }

    private function findCertPath(string $domain): ?string
    {
        $suffixes = ['', '-0001', '-0002', '-0003'];

        foreach ($suffixes as $suffix) {
            $path = "/etc/letsencrypt/live/{$domain}{$suffix}";
            if (file_exists("{$path}/fullchain.pem")) {
                return $path;
            }
        }

        return null;
    }

    private function generateSSL(string $domain): array
    {
        $safeDomain = escapeshellarg($domain);
        $safeEmail  = escapeshellarg($this->adminEmail);

        $sslOutput = shell_exec("sudo certbot certonly --nginx -d {$safeDomain} --non-interactive --agree-tos -m {$safeEmail} 2>&1");
        Log::info("SSL output for {$domain}: " . $sslOutput);

        shell_exec("sudo chown -R root:{$this->webUser} /etc/letsencrypt/live/{$domain}/ 2>&1");
        shell_exec("sudo chown -R root:{$this->webUser} /etc/letsencrypt/archive/{$domain}/ 2>&1");
        shell_exec("sudo chmod 750 /etc/letsencrypt/live/{$domain}/ 2>&1");
        shell_exec("sudo chmod 750 /etc/letsencrypt/archive/{$domain}/ 2>&1");

        $certPath = $this->findCertPath($domain);

        if (!$certPath) {
            return [
                'success'    => false,
                'message'    => "SSL generation failed for {$domain}",
                'ssl_output' => $sslOutput ?? ''
            ];
        }

        return [
            'success'  => true,
            'certPath' => $certPath
        ];
    }

    private function reloadNginx(): array
    {
        $testOutput = shell_exec("sudo nginx -t 2>&1");

        if (str_contains($testOutput, 'failed') || str_contains($testOutput, 'error')) {
            Log::error("Nginx config test failed: " . $testOutput);
            return [
                'success' => false,
                'message' => "Nginx config test failed: " . $testOutput
            ];
        }

        $reloadOutput = shell_exec("sudo systemctl reload nginx 2>&1");

        if (str_contains($reloadOutput ?? '', 'failed') || str_contains($reloadOutput ?? '', 'error')) {
            Log::error("Nginx reload failed: " . $reloadOutput);
            return [
                'success' => false,
                'message' => "Nginx reload failed: " . $reloadOutput
            ];
        }

        return ['success' => true];
    }

    private function generateNginxConfig(string $domain, string $certPath): string
    {
        return "
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
}";
    }
}
