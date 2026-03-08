<?php

namespace App\Services\TenantService;

use App\Models\Central\UserPackage;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TenantService
{
    public function createTenant(array $data)
    {

        try {
            // 1. Create Tenant Record in Central DB
            $tenantId = DB::connection('LMS_CENTER')->table('tenants')->insertGetId([
                'uuid'       => Str::uuid(),
                'name'       => $data['name'] ?? $data['username'],
                'domain'     => $data['domain'],
                'db_name'    => 'tenant_' . Str::slug($data['name'] ?? $data['username']) . '_' . Str::random(4),
                'db_user'    => 'user_' . Str::random(5),
                'db_pass'    => Str::random(12),
                'db_host'    => config('database.connections.LMS_CENTER.host', '127.0.0.1'),
                'active'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $tenant = DB::connection('LMS_CENTER')->table('tenants')->find($tenantId);
            Log::info("Step 1: Tenant record created. ID: {$tenantId}");

            // 2. Create Database and User
            $this->createDatabaseAndUser($tenant);
            Log::info("Step 2: Database created: {$tenant->db_name}");

            // 3. Switch Connection
            $this->switchToTenantDatabase($tenant);
            Log::info("Step 3: Switched connection to tenant database");

            // 4. Run Migrations
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path'     => 'database/migrations',
                '--force'    => true,
            ]);
            Log::info("Step 4: Migrations completed successfully");

            // 5. Seed Initial Data
            $this->seedTenantData($data);
            Log::info("Step 5: Admin, Package, and Features seeded successfully");

            Log::info("=== Tenant Creation Success: {$tenant->domain} ===");
            return $tenant;
        } catch (Exception $e) {
            Log::error("!!! Tenant Creation Failed: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    protected function createDatabaseAndUser($tenant)
    {
        DB::statement("CREATE DATABASE `{$tenant->db_name}`");
        DB::statement("CREATE USER '{$tenant->db_user}'@'%' IDENTIFIED BY '{$tenant->db_pass}'");
        DB::statement("GRANT ALL PRIVILEGES ON `{$tenant->db_name}`.* TO '{$tenant->db_user}'@'%'");
        DB::statement("FLUSH PRIVILEGES");
    }

    protected function switchToTenantDatabase($tenant)
    {
        config([
            'database.connections.tenant.host'     => $tenant->db_host,
            'database.connections.tenant.database' => $tenant->db_name,
            'database.connections.tenant.username' => $tenant->db_user,
            'database.connections.tenant.password' => $tenant->db_pass,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    protected function seedTenantData(array $data)
    {
        // 1. Insert Admin User
        $userId = DB::connection('tenant')->table('users')->insertGetId([
            'name'          => $data['name'],
            'email'         => $data['email'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'password'      => $data['password'],
            'username'      => $data['user_name'] ?? $data['name'],
            'country_code'  => $data['country_code'] ?? null,
            'phone_academy' => $data['phone_academy'] ?? null,
            'specialties'   => $data['specialties'] ?? null,
            'role'          => 'admin',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
        Log::info("--- Admin user created. ID: {$userId} in tenant DB");

        // 2. Fetch Active Package from Central DB
        $centralUserId = $data['user_id'] ?? null;

        $userPackage = UserPackage::with(['package.packageFeatures.feature'])
            ->where('user_id', $centralUserId)
            ->where('status', 'active')
            ->first();

        if ($userPackage && $userPackage->package) {
            Log::info("--- Active package foundSSSSSSSSSSS: ", [$userPackage]);

            // A. Seed User Package
            DB::connection('tenant')->table('user_packages')->insert([
                'user_id'    => $userId,
                'package_id' => $userPackage->package_id,
                'package_name' => $userPackage->title ?? $userPackage->package->titile ?? 'Unknown Package',
                'status'     => 'active',
                'price'      => $userPackage->price,
                'start_date' => now(),
                'end_date'   => now()->addDays($userPackage->duration_months * 30), // Assuming 30 days per month
                'created_at' => now(),
            ]);
            Log::info("--- Package linked to user in tenant DB");

            // B. Seed Feature Usage
            $packageFeatures = $userPackage->package->packageFeatures;

            if ($packageFeatures->isEmpty()) {
                Log::warning("--- Warning: No features found for this package");
            }

            foreach ($packageFeatures as $item) {
                $feature = $item->feature;
                $isNumeric = ($item->value == -1 || $item->value > 1);
                $featureType = $isNumeric ? 'numeric' : 'boolean';

                if ($feature) {
                    DB::connection('tenant')->table('tenant_feature_usage')->insert([
                        'feature_slug' => $feature->key,
                        'total_limit'  => $item->value,
                        'used_amount'  => 0,
                        'type'         => $featureType,
                        'is_enabled'   => $item->value != 0,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    Log::info("--- Seeded feature: {$feature->slug} | Limit: {$item->value}");
                }
            }
        } else {
            Log::warning("--- Warning: No active package found for Central User ID: {$centralUserId}");
        }
    }
}
