<?php

namespace App\Http\Controllers\Admin\CustomDomain;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomDomain\CustomDomainRequest;
use App\Services\DomainService\DomainService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
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

        $domain    = $request->domain;
        $tenant    = app('tenant');
        $tenantId  = $tenant->id;
        $oldDomain = $tenant->domain;

        Log::alert("Domain Setup Request", [
            'domain'     => $domain,
            'tenant_id'  => $tenantId,
            'old_domain' => $oldDomain,
        ]);

        // ✅ تحقق إن الدومين مش موجود عند tenant تاني
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

        if (str_ends_with($domain, '.darab.academy')) {
            try {
                $this->cleanupOldDomain($oldDomain);

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
                    'message' => "Subdomain registered successfully."
                ], 200);
            } catch (\Exception $e) {
                Log::error("Failed to update tenant domain in DB: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => "Failed to update record. Please contact support."
                ], 500);
            }
        }

        // ✅ امسح الدومين القديم قبل ما تحط الجديد
        $this->cleanupOldDomain($oldDomain);

        // ✅ دومين خارجي يكمل الـ SSL والـ Nginx كالعادة
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
                'message' => "Domain configured and updated successfully in our records."
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update tenant domain in DB: " . $e->getMessage());
            return response()->json([
                'success' => true,
                'message' => "Domain setup on server done, but failed to update record. Please contact support."
            ], 500);
        }
    }

    // ============================================================
    // Private Helpers
    // ============================================================

    private function cleanupOldDomain(string $oldDomain): void
    {
        if (empty($oldDomain) || $oldDomain === 'darab.academy') {
            Log::info("Skipping cleanup for protected domain: {$oldDomain}");
            return;
        }

        // ✅ لو subdomain داخلي - امسح Nginx بس بدون SSL
        if (str_ends_with($oldDomain, '.darab.academy')) {
            $nginxConfig = "/etc/nginx/sites-enabled/{$oldDomain}";
            if (file_exists($nginxConfig)) {
                shell_exec("sudo rm -f " . escapeshellarg($nginxConfig) . " 2>&1");
                Log::info("Deleted Nginx config for internal subdomain: {$oldDomain}");
                shell_exec("sudo nginx -t && sudo systemctl reload nginx 2>&1");
            }
            return;
        }

        // ✅ دومين خارجي - امسح SSL و Nginx
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
