<?php

namespace App\Services\TenantService;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;

class TenantService
{
    public function createTenant(array $data)
    {
        $tenantId = DB::connection('LMS_CENTER')->table('tenants')->insertGetId([
            'uuid' => Str::uuid(),
            'name' => $data['name'],
            'domain' => $data['domain'],
            'db_name' => 'tenant_' . Str::slug($data['name']),
            'db_user' => 'user_' . Str::random(5),
            'db_pass' => Str::random(12),
            'db_host' => '127.0.0.1',
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenant = DB::connection('LMS_CENTER')->table('tenants')->where('id', $tenantId)->first();

        // -------------------------------
        // -------------------------------
        DB::statement("CREATE DATABASE `{$tenant->db_name}`");

        // -------------------------------
        // -------------------------------
        DB::statement("CREATE USER '{$tenant->db_user}'@'%' IDENTIFIED BY '{$tenant->db_pass}'");
        DB::statement("GRANT ALL PRIVILEGES ON `{$tenant->db_name}`.* TO '{$tenant->db_user}'@'%'");
        DB::statement("FLUSH PRIVILEGES");

        // -------------------------------
        // -------------------------------
        config([
            'database.connections.tenant.host' => $tenant->db_host,
            'database.connections.tenant.database' => $tenant->db_name,
            'database.connections.tenant.username' => $tenant->db_user,
            'database.connections.tenant.password' => $tenant->db_pass,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        // -------------------------------
        // -------------------------------

        Telescope::stopRecording();
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        // -------------------------------
        // -------------------------------
        // Artisan::call('db:seed', [
        //     '--class' => 'TenantSeeder', 
        //     '--database' => 'tenant',
        //     '--force' => true,
        // ]);

        return $tenant;
    }
}
