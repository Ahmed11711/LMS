<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ResolveTenant
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $tenant = cache()->remember("tenant_meta_{$host}", now()->addDay(), function () use ($host) {
            return DB::connection('LMS_CENTER')
                ->table('tenants')
                ->where('domain', $host)
                ->where('active', 1)
                ->first();
        });

        if (!$tenant) {
            cache()->forget("tenant_meta_{$host}");
            abort(403, 'Tenant not found or inactive.');
        }

        Config::set('database.connections.tenant.host', $tenant->db_host);
        Config::set('database.connections.tenant.database', $tenant->db_name);
        Config::set('database.connections.tenant.username', $tenant->db_user);
        Config::set('database.connections.tenant.password', $tenant->db_pass);

        DB::purge('tenant');

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
