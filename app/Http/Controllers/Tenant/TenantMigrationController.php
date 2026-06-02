<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantService\TenantMigrationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class TenantMigrationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private TenantMigrationService $service) {}

    /**
     * POST /super-admin/tenants/migrate
     * migrate كل الـ tenants
     */
    public function migrateAll()
    {

        try {
            $result = $this->service->migrateAll();

            if ($result['summary']['total'] === 0) {
                return $this->errorResponse('No active tenants found.', 404);
            }

            $s = $result['summary'];
            return $this->successResponse($result, "Migration completed. Success: {$s['success']} | Failed: {$s['failed']}");
        } catch (Throwable $e) {
            Log::error('migrateAll error: ' . $e->getMessage());
            return $this->errorResponse('Migration process failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /super-admin/tenants/{domain}/migrate
     * migrate tenant واحد
     */
    public function migrateSingle(string $domain): JsonResponse
    {
        try {
            $result = $this->service->migrateByDomain($domain);
            return $this->successResponse($result, "Migration completed for [{$domain}].");
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Throwable $e) {
            Log::error("migrateSingle error [{$domain}]: " . $e->getMessage());
            return $this->errorResponse('Migration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /academy/migrate
     * migrate الـ tenant الحالي (داخل الـ ResolveTenant middleware)
     */
    public function migrateCurrentTenant(): JsonResponse
    {
        try {
            $result = $this->service->migrateCurrentTenant();
            return $this->successResponse($result, 'Migration completed.');
        } catch (Throwable $e) {
            Log::error('migrateCurrentTenant error: ' . $e->getMessage());
            return $this->errorResponse('Migration failed: ' . $e->getMessage(), 500);
        }
    }
}
