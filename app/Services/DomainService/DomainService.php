<?php

namespace App\Services\DomainService;

use Illuminate\Support\Facades\Log;

class DomainService
{
    private string $adminEmail = 'admin@darab.academy';
    private string $webUser    = 'hestiamail';

    public function setupDomain(string $domain): array
    {
        try {
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

            Log::info("Nginx config written for {$domain} at {$certPath}");

            // 5. Reload Nginx
            $reloadResult = $this->reloadNginx();
            if (!$reloadResult['success']) {
                return $reloadResult;
            }

            return [
                'success' => true,
                'message' => "Domain {$domain} configured successfully"
            ];
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

    private function isDomainValid(string $domain): array
    {
        // 1. Validate domain format
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return [
                'valid'   => false,
                'message' => "Invalid domain format"
            ];
        }

        // 2. Resolve domain IP
        $domainIp = gethostbyname($domain);
        if ($domainIp === $domain) {
            return [
                'valid'   => false,
                'message' => "Domain {$domain} does not resolve to any server"
            ];
        }

        // 3. Get server public IP
        $serverIp = trim(shell_exec("curl -4 -s ifconfig.me 2>&1"));

        // 4. Ensure domain points to this server
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

        // Fix permissions so web user can read the certificate
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
