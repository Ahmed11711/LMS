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

        $domain   = $request->domain;
        $tenantId = $request->tenant_id;

        // ✅ لو subdomain داخلي اتخطى الـ SSL وروح للـ DB مباشرة
        if (str_ends_with($domain, '.darab.academy')) {
            try {
                DB::connection('LMS_CENTER')
                    ->table('tenants')
                    ->where('id', $tenantId)
                    ->update([
                        'domain'     => $domain,
                        'updated_at' => now()
                    ]);

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
}
