<?php

namespace App\Services\TenantService;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TenantMigrationService
{
    /**
     * migrate كل الـ tenants النشطة
     */
    public function migrateAll(): array
    {
        $tenants = DB::connection('LMS_CENTER')
            ->table('tenants')
            ->where('active', 1)
            ->get();

        if ($tenants->isEmpty()) {
            return [
                'summary' => ['total' => 0, 'success' => 0, 'failed' => 0],
                'tenants' => [],
            ];
        }

        $results = [];

        foreach ($tenants as $tenant) {
            $results[] = $this->migrateTenant($tenant);
        }

        $successCount = collect($results)->where('status', 'success')->count();
        $failedCount  = collect($results)->where('status', 'failed')->count();

        return [
            'summary' => [
                'total'   => $tenants->count(),
                'success' => $successCount,
                'failed'  => $failedCount,
            ],
            'tenants' => $results,
        ];
    }

    /**
     * migrate tenant واحد عن طريق الـ domain
     */
    public function migrateByDomain(string $domain): array
    {
        $tenant = DB::connection('LMS_CENTER')
            ->table('tenants')
            ->where('domain', $domain)
            ->where('active', 1)
            ->first();

        if (!$tenant) {
            throw new \RuntimeException("Tenant [{$domain}] not found or inactive.");
        }

        return $this->migrateTenant($tenant);
    }

    /**
     * migrate الـ tenant الحالي (اللي الـ ResolveTenant ضبطه بالفعل)
     */
    public function migrateCurrentTenant(): array
    {
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--force'    => true,
        ]);

        $output = trim(Artisan::output());

        return [
            'output' => $output ?: 'Nothing to migrate.',
        ];
    }

    /**
     * الـ core logic: ضبط الـ connection وتشغيل الـ migrate
     */
    public function migrateTenant(object $tenant): array
    {
        $result = [
            'domain'  => $tenant->domain,
            'db_name' => $tenant->db_name,
            'status'  => null,
            'output'  => null,
            'error'   => null,
        ];

        try {
            $this->setTenantConnection($tenant);

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force'    => true,
            ]);

            $output = trim(Artisan::output());

            $result['status'] = 'success';
            $result['output'] = $output ?: 'Nothing to migrate.';
        } catch (Throwable $e) {
            Log::error("Tenant migration failed [{$tenant->domain}]: " . $e->getMessage());

            $result['status'] = 'failed';
            $result['error']  = $e->getMessage();
        }

        return $result;
    }

    /**
     * ضبط الـ tenant connection
     */
    private function setTenantConnection(object $tenant): void
    {
        Config::set('database.connections.tenant.driver',      'pgsql');
        Config::set('database.connections.tenant.host',        $tenant->db_host);
        Config::set('database.connections.tenant.port',        5432);
        Config::set('database.connections.tenant.database',    $tenant->db_name);
        Config::set('database.connections.tenant.username',    $tenant->db_user);
        Config::set('database.connections.tenant.password',    $tenant->db_pass);
        Config::set('database.connections.tenant.search_path', 'public');
        Config::set('database.connections.tenant.sslmode',     'prefer');

        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
