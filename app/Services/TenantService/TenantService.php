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
                'user_id'    => $data['user_id'],
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


            // 2. Create Database and User
            $this->createDatabaseAndUser($tenant);

            // 3. Switch Connection
            $this->switchToTenantDatabase($tenant);

            // 4. Run Migrations
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path'     => 'database/migrations',
                '--force'    => true,
            ]);

            // 5. Seed Initial Data
            $this->seedTenantData($data);
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantSeeder',
                '--database' => 'tenant',
                '--force' => true,
            ]);
            return $tenant;
        } catch (Exception $e) {
            Log::error("!!! Tenant Creation Failed: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    // m SQL
    // protected function createDatabaseAndUser($tenant)
    // {
    //     DB::statement("CREATE DATABASE `{$tenant->db_name}`");
    //     DB::statement("CREATE USER '{$tenant->db_user}'@'%' IDENTIFIED BY '{$tenant->db_pass}'");
    //     DB::statement("GRANT ALL PRIVILEGES ON `{$tenant->db_name}`.* TO '{$tenant->db_user}'@'%'");
    //     DB::statement("FLUSH PRIVILEGES");
    // }

    protected function createDatabaseAndUser($tenant)
    {
        // ✅ PostgreSQL syntax
        DB::statement("CREATE DATABASE \"{$tenant->db_name}\"");
        DB::statement("CREATE USER \"{$tenant->db_user}\" WITH PASSWORD '{$tenant->db_pass}'");
        DB::statement("GRANT ALL PRIVILEGES ON DATABASE \"{$tenant->db_name}\" TO \"{$tenant->db_user}\"");
        DB::statement("ALTER DATABASE \"{$tenant->db_name}\" OWNER TO \"{$tenant->db_user}\"");
        DB::statement("GRANT CREATE ON SCHEMA public TO \"{$tenant->db_user}\"");
    }

    /// SQL
    // protected function switchToTenantDatabase($tenant)
    // {
    //     config([
    //         'database.connections.tenant.host'     => $tenant->db_host,
    //         'database.connections.tenant.database' => $tenant->db_name,
    //         'database.connections.tenant.username' => $tenant->db_user,
    //         'database.connections.tenant.password' => $tenant->db_pass,
    //     ]);

    //     DB::purge('tenant');
    //     DB::reconnect('tenant');
    // }

    protected function switchToTenantDatabase($tenant)
    {
        config([
            'database.connections.tenant.driver'   => 'pgsql', // ✅ زود السطر ده
            'database.connections.tenant.host'     => $tenant->db_host,
            'database.connections.tenant.database' => $tenant->db_name,
            'database.connections.tenant.username' => $tenant->db_user,
            'database.connections.tenant.password' => $tenant->db_pass,
            'database.connections.tenant.port'     => 5432, // ✅ زود ده برضو
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }
    // protected function seedTenantData(array $data)
    // {
    //     // 1. Insert Admin User
    //     $userId = DB::connection('tenant')->table('users')->insertGetId([
    //         'name'          => $data['name'],
    //         'email'         => $data['email'] ?? null,
    //         'phone'         => $data['phone'] ?? null,
    //         'password'      => $data['password'],
    //         'username'      => $data['user_name'] ?? $data['name'],
    //         'country_code'  => $data['country_code'] ?? null,
    //         'phone_academy' => $data['phone_academy'] ?? null,
    //         'specialties'   => $data['specialties'] ?? null,
    //         'role'          => 'admin',
    //         'created_at'    => now(),
    //         'updated_at'    => now(),
    //     ]);
    //     Log::info("--- Admin user created. ID: {$userId} in tenant DB");

    //     // 2. Fetch Active Package from Central DB
    //     $centralUserId = $data['user_id'] ?? null;

    //     $userPackage = UserPackage::on('LMS_CENTER')
    //         ->with(['package.packageFeatures.feature'])
    //         ->where('user_id', $centralUserId)
    //         ->where(function ($q) {
    //             $q->where('status', 'active')->orWhere('active', 1);
    //         })
    //         ->first();

    //     if ($userPackage && $userPackage->package) {
    //         Log::info("--- Active package foundSSSSSSSSSSS: ", [$userPackage]);

    //         // A. Seed User Package
    //         DB::connection('tenant')->table('user_packages')->insert([
    //             'user_id'    => $userId,
    //             'package_id' => $userPackage->package_id,
    //             'package_name' => $userPackage->title ?? $userPackage->package->titile ?? 'Unknown Package',
    //             'status'     => 'active',
    //             'price'      => $userPackage->price,
    //             'start_date' => now(),
    //             'end_date'   => now()->addDays($userPackage->duration_months * 30), // Assuming 30 days per month
    //             'created_at' => now(),
    //         ]);
    //         Log::info("--- Package linked to user in tenant DB");

    //         // B. Seed Feature Usage
    //         $packageFeatures = $userPackage->package->packageFeatures;

    //         if ($packageFeatures->isEmpty()) {
    //             Log::warning("--- Warning: No features found for this package");
    //         }

    //         foreach ($packageFeatures as $item) {
    //             $feature = $item->feature;
    //             $isNumeric = ($item->value == -1 || $item->value > 1);
    //             $featureType = $isNumeric ? 'numeric' : 'boolean';

    //             if ($feature) {
    //                 DB::connection('tenant')->table('tenant_feature_usage')->insert([
    //                     'feature_slug' => $feature->key,
    //                     'total_limit'  => $item->value,
    //                     'used_amount'  => 0,
    //                     'type'         => $featureType,
    //                     'is_enabled'   => $item->value != 0,
    //                     'created_at'   => now(),
    //                     'updated_at'   => now(),
    //                 ]);
    //                 Log::info("--- Seeded feature: {$feature->slug} | Limit: {$item->value}");
    //             }
    //         }
    //     } else {
    //         Log::warning("--- Warning: No active package found for Central User ID: {$centralUserId}");
    //     }
    // }
    protected function seedTenantData(array $data)
    {
        // 1. Insert Admin User in Tenant
        $userId = DB::connection('tenant')->table('users')->insertGetId([
            'name'          => $data['name'],
            'email' => $data['email'] ?? ($data['user_email'] ?? null),
            'phone' => $data['phone'] ?? null,
            'password'      => $data['password'],
            'username'      => $data['user_name'] ?? $data['username'],
            'role'          => 'admin',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // 2. Use Passed Data directly
        $userPackage = $data['passed_package'] ?? null;
        $features    = $data['passed_features'] ?? collect();

        if ($userPackage) {
            // A. Insert User Package
            DB::connection('tenant')->table('user_packages')->insert([
                'user_id'      => $userId,
                'package_id'   => $userPackage->package_id,
                'package_name' => $userPackage->package_name ?? 'Trial Package',
                'status'       => 'active',
                'price'        => $userPackage->price,
                'start_date'   => now(),
                'end_date'     => now()->addDays(30),
                'created_at'   => now(),
            ]);

            // B. Insert Features
            foreach ($features as $f) {
                DB::connection('tenant')->table('tenant_feature_usage')->updateOrInsert(
                    ['feature_slug' => $f->key],
                    [
                        'total_limit'  => $f->value,
                        'used_amount'  => 0,
                        'type'         => ($f->value == -1 || (int)$f->value > 1) ? 'numeric' : 'boolean',
                        'is_enabled'   => $f->value != 0,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]
                );
            }
            Log::info("Tenant Seeding Successful using Passed Data for User: {$data['user_id']}");
        } else {
            Log::error("Seeding Failed: Passed package data is missing.");
        }
    }
}
