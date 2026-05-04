<?php

namespace App\Services\DomainService;

use Illuminate\Support\Facades\Log;

class DomainService
{
    public function setupDomain(string $domain): array
    {
        try {
            // 1. تأكد إن الدومين صح
            $validation = $this->isDomainValid($domain);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // 2. عمل SSL
            $sslOutput = shell_exec("sudo certbot certonly --nginx -d {$domain} --non-interactive --agree-tos -m admin@darab.academy 2>&1");
            Log::info("SSL Output for {$domain}: " . $sslOutput);

            // 3. تأكد إن الـ SSL اتعمل صح
            if (!file_exists("/etc/letsencrypt/live/{$domain}/fullchain.pem")) {
                return [
                    'success'    => false,
                    'message'    => "SSL فشل للدومين {$domain}",
                    'ssl_output' => $sslOutput
                ];
            }

            // 4. كتابة Nginx Config
            $config = $this->generateNginxConfig($domain);
            file_put_contents("/etc/nginx/sites-enabled/{$domain}", $config);

            // 5. Reload Nginx
            shell_exec("sudo systemctl reload nginx 2>&1");

            return [
                'success' => true,
                'message' => "تم إعداد الدومين {$domain} بنجاح 🎉"
            ];
        } catch (\Exception $e) {
            Log::error("Domain setup failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function isDomainValid(string $domain): array
    {
        // 1. تأكد إن صيغة الدومين صح
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return [
                'valid'   => false,
                'message' => "صيغة الدومين غلط"
            ];
        }

        // 2. بياخد الـ IP بتاع الدومين
        $domainIp = gethostbyname($domain);

        // لو gethostbyname فشل بيرجع نفس الـ domain
        if ($domainIp === $domain) {
            return [
                'valid'   => false,
                'message' => "الدومين {$domain} مش موجود أو مش بيشاور على أي سيرفر"
            ];
        }

        // 3. بياخد الـ IPv4 بتاع السيرفر بتاعنا
        $serverIp = trim(shell_exec("curl -4 -s ifconfig.me 2>&1"));

        // 4. بيتأكد إنهم نفس الـ IP
        if ($domainIp !== $serverIp) {
            return [
                'valid'   => false,
                'message' => "الدومين {$domain} مش بيشاور على السيرفر بتاعنا. IP بتاعه: {$domainIp}, IP السيرفر: {$serverIp}"
            ];
        }

        return [
            'valid'   => true,
            'message' => "الدومين صح ✅"
        ];
    }

    private function generateNginxConfig(string $domain): string
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

    ssl_certificate /etc/letsencrypt/live/{$domain}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/{$domain}/privkey.pem;
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
