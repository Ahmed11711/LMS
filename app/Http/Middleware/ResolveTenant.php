<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $host = $request->header('X-Tenant-Key') ?? $request->query('tenant') ?? $request->getHost();

        $tenant = Cache::remember("tenant_meta_{$host}", now()->addDay(), function () use ($host) {
            return DB::connection('LMS_CENTER')
                ->table('tenants')
                ->where('domain', $host)
                ->where('active', 1)
                ->first();
        });

        if (!$tenant) {
            Cache::forget("tenant_meta_{$host}");
            abort(403, 'Tenant not found or inactive.');
        }

        Config::set('database.connections.tenant.host', $tenant->db_host);
        Config::set('database.connections.tenant.database', $tenant->db_name);
        Config::set('database.connections.tenant.username', $tenant->db_user);
        Config::set('database.connections.tenant.password', $tenant->db_pass);

        DB::purge('tenant');
        Config::set('database.default', 'tenant');
        DB::reconnect('tenant');

        // 1. تثبيت الـ Tenant في الـ Container
        app()->instance('tenant', $tenant);

        // 2. تحديث الـ Prefix في الـ Config
        $prefix = 'lms_tenant_' . $tenant->id . ':';
        config(['database.redis.options.prefix' => $prefix]);

        // 3. الحل الجذري: إعادة بناء الـ Redis Manager بالكامل
        if (app()->bound('redis')) {
            // بنمسح النسخة القديمة من الـ Container تماماً
            app()->forgetInstance('redis');

            // بنجبر الـ Container إنه يعمل Re-bind للـ RedisManager بالإعدادات الجديدة
            app()->bind('redis', function ($app) {
                return new \Illuminate\Redis\RedisManager($app, $app['config']['database.redis.client'], $app['config']['database.redis']);
            });
        }

        return $next($request);
    }
}
