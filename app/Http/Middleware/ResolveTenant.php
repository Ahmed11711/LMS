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

        // بدل السطر القديم، استخدم ده:
        $tenant = Cache::store('file')->remember("tenant_meta_{$host}", now()->addDay(), function () use ($host) {
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

        // ... (الجزء الأول من الكود بتاعك زي ما هو)

        app()->instance('tenant', $tenant);

        // 1. ضبط الـ Prefix الجديد
        $prefix = 'lms_tenant_' . $tenant->id . ':';
        config(['database.redis.options.prefix' => $prefix]);

        if (app()->bound('redis')) {
            // 2. مسح النسخة القديمة المضروبة
            app()->forgetInstance('redis');

            // 3. إعادة تعريف الـ Redis Manager يدوياً لضمان قراءة الـ Config الجديد
            app()->singleton('redis', function ($app) {
                $config = $app->make('config')->get('database.redis');
                return new \Illuminate\Redis\RedisManager($app, $config['client'], $config);
            });

            // 4. الخطوة السحرية: عمل Resolve للـ Redis فوراً
            // دي بتخلي الـ Redis Manager يفتح الـ Connector بتاعه دلوقتي بدل ما يستنى JWT
            app()->make('redis');
        }

        return $next($request);

        return $next($request);
    }
}
