<?php

namespace App\Http\Controllers\Admin\CustomDomain;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomDomain\CustomDomainRequest;
use App\Services\DomainService\DomainService;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomDomainController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        public DomainService $domainService
    ) {}

    public function setup(CustomDomainRequest $request)
    {
        $request->validated();


        $domain   = strtolower(trim($request->domain));
        $tenant   = app('tenant');
        $tenantId = $tenant->id;
        $oldDomain = $tenant->domain;

        Log::alert("Domain Setup Request", [
            'domain'     => $domain,
            'tenant_id'  => $tenantId,
            'old_domain' => $oldDomain,
        ]);

        if ($this->isProtectedDomain($domain)) {
            return response()->json([
                'success' => false,
                'message' => "This domain is protected and cannot be used."
            ], 422);
        }

        $exists = DB::connection('LMS_CENTER')
            ->table('tenants')
            ->where('domain', $domain)
            ->where('id', '!=', $tenantId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "This domain is already taken by another account. Please choose a different one."
            ], 422);
        }

        // ✅ امسح الدومين القديم
        $this->cleanupOldDomain($oldDomain);

        // ✅ Setup الدومين الجديد (subdomain أو external)
        $result = $this->domainService->setupDomain($domain);
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        try {
            DB::connection('LMS_CENTER')
                ->table('tenants')
                ->where('id', $tenantId)
                ->update([
                    'domain'     => $domain,
                    'updated_at' => now()
                ]);

            cache()->forget("tenant_meta_{$oldDomain}");

            return response()->json([
                'success' => true,
                'message' => "Domain configured successfully."
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update tenant domain in DB: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Domain setup done, but failed to update record. Please contact support."
            ], 500);
        }
    }

    // ============================================================
    // Private Helpers
    // ============================================================

    private function isProtectedDomain(string $domain): bool
    {
        $protectedDomains = [
            'darab.academy',
            'www.darab.academy',
            'api.darab.academy',
            'mail.darab.academy',
            'admin.darab.academy',
        ];

        return in_array(strtolower($domain), $protectedDomains);
    }

    private function cleanupOldDomain(string $oldDomain): void
    {
        if (empty($oldDomain) || $this->isProtectedDomain($oldDomain)) {
            Log::info("Skipping cleanup for protected or empty domain: {$oldDomain}");
            return;
        }

        // Subdomain داخلي - امسح Nginx بس
        if (str_ends_with($oldDomain, '.darab.academy')) {
            $nginxConfig = "/etc/nginx/sites-enabled/{$oldDomain}";
            if (file_exists($nginxConfig)) {
                shell_exec("sudo rm -f " . escapeshellarg($nginxConfig) . " 2>&1");
                Log::info("Deleted Nginx config for internal subdomain: {$oldDomain}");
                shell_exec("sudo nginx -t && sudo systemctl reload nginx 2>&1");
            }
            return;
        }

        // External domain - امسح SSL + Nginx
        $suffixes = ['', '-0001', '-0002', '-0003'];
        foreach ($suffixes as $suffix) {
            $certName = $oldDomain . $suffix;
            $certPath = "/etc/letsencrypt/live/{$certName}";

            if (file_exists($certPath)) {
                $safeCert = escapeshellarg($certName);
                shell_exec("sudo certbot delete --cert-name {$safeCert} --non-interactive 2>&1");
                Log::info("Deleted SSL cert: {$certName}");
            }
        }

        $nginxConfig = "/etc/nginx/sites-enabled/{$oldDomain}";
        if (file_exists($nginxConfig)) {
            shell_exec("sudo rm -f " . escapeshellarg($nginxConfig) . " 2>&1");
            Log::info("Deleted Nginx config for: {$oldDomain}");
        }

        shell_exec("sudo nginx -t && sudo systemctl reload nginx 2>&1");
        Log::info("Nginx reloaded after cleanup of: {$oldDomain}");
    }
}
