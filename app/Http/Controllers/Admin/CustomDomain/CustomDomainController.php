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
        private DomainService $domainService
    ) {}

    public function setup(CustomDomainRequest $request): \Illuminate\Http\JsonResponse
    {
        $domain    = strtolower(trim($request->validated()['domain']));
        $tenant    = app('tenant');
        $tenantId  = $tenant->id;
        $oldDomain = $tenant->domain;

        Log::alert("Domain Setup Request", [
            'domain'     => $domain,
            'tenant_id'  => $tenantId,
            'old_domain' => $oldDomain,
        ]);

        // 1. Protected domain check
        if ($this->domainService->isProtectedDomain($domain)) {
            return response()->json([
                'success' => false,
                'message' => "This domain is protected and cannot be used."
            ], 422);
        }

        // 2. Uniqueness check
        $exists = DB::connection('LMS_CENTER')
            ->table('tenants')
            ->where('domain', $domain)
            ->where('id', '!=', $tenantId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "This domain is already taken. Please choose a different one."
            ], 422);
        }

        // 3. Cleanup old domain
        if ($oldDomain && $oldDomain !== $domain) {
            $this->domainService->cleanupDomain($oldDomain);
        }

        // 4. Setup new domain
        $result = $this->domainService->setupDomain($domain);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        // 5. Persist to DB
        try {
            DB::connection('LMS_CENTER')
                ->table('tenants')
                ->where('id', $tenantId)
                ->update(['domain' => $domain, 'updated_at' => now()]);

            cache()->forget("tenant_meta_{$oldDomain}");

            return response()->json([
                'success' => true,
                'message' => "Domain configured successfully."
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update tenant domain: " . $e->getMessage());

            // Rollback: restore old domain config
            if ($oldDomain) {
                $this->domainService->setupDomain($oldDomain);
            }

            return response()->json([
                'success' => false,
                'message' => "Domain setup done, but failed to save. Please contact support."
            ], 500);
        }
    }
}
