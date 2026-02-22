<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResolveTenant
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $tenant = cache()->remember("tenant_meta_{$host}", 86400, function () use ($host) {
            return DB::connection('LMS_CENTER')
                ->table('tenants')
                ->where('domain', $host)
                ->where('active', 1)
                ->first();
        });

        // $tenant = DB::connection('LMS_CENTER')
        //     ->table('tenants')
        //     ->where('domain', $host)
        //     ->where('active', 1)
        //     ->first();

        if (!$tenant) {
            abort(403, 'Tenant not found');
        }

        config([
            'database.connections.tenant.host' => $tenant->db_host,
            'database.connections.tenant.database' => $tenant->db_name,
            'database.connections.tenant.username' => $tenant->db_user,
            'database.connections.tenant.password' => $tenant->db_pass,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
