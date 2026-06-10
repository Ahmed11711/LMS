<?php

namespace App\Http\Middleware;

use App\Models\Central\FeaturePackage;
use App\Models\Central\Package;
use App\Models\Tenant as ModelsTenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SyncFeaturePackageAfterResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldSync($request, $response)) {
            return;
        }

        try {

            if ($request->method() === 'DELETE') {
                $this->handleDeleteSync($request);
                return;
            }

            $featurePackageId = $request->route('feature_package');

            if (is_null($featurePackageId) && $request->method() === 'POST') {
                $responseData     = json_decode($response->getContent(), true);
                $featurePackageId = $responseData['data']['id'] ?? $responseData['id'] ?? null;
            }

            if (is_null($featurePackageId)) {
                Log::error('Feature package id is null, aborting sync');
                return;
            }

            $featurePackage = FeaturePackage::find($featurePackageId);

            if (! $featurePackage) {
                Log::error('FeaturePackage not found', ['id' => $featurePackageId]);
                return;
            }

            $package = Package::with(['featurePackages.feature'])
                ->find($featurePackage->package_id);

            if (! $package) {
                Log::error('Package not found', ['package_id' => $featurePackage->package_id]);
                return;
            }

            $featuresMap = $package->featurePackages->mapWithKeys(function ($fp) {
                return [
                    $fp->feature->key => [
                        'total_limit' => $fp->value,
                        'type'        => $fp->key_feature ?? 'numeric',
                        'status'      => ($fp->value == 0) ? 'inactive' : 'active',
                    ]
                ];
            });

            $currentKeys = $featuresMap->keys()->toArray();

            $tenants = ModelsTenant::whereHas('subscription', function ($query) use ($package) {
                $query->where('package_id', $package->id)
                    ->where('active', true);
            })->get();

            foreach ($tenants as $tenant) {
                try {
                    $this->connectToTenant($tenant);
                } catch (Throwable $e) {
                    Log::error('Failed to connect to tenant DB', [
                        'tenant_id' => $tenant->id,
                        'message'   => $e->getMessage(),
                    ]);
                    continue;
                }

                try {
                    $this->syncTenantFeatures($featuresMap, $currentKeys);
                } catch (Throwable $e) {
                    Log::error('Failed to sync features for tenant', [
                        'tenant_id' => $tenant->id,
                        'message'   => $e->getMessage(),
                        'line'      => $e->getLine(),
                        'file'      => $e->getFile(),
                    ]);
                    continue;
                }
            }
        } catch (Throwable $e) {
            Log::error('Feature package sync failed', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    private function handleDeleteSync(Request $request): void
    {
        $deletedFeatureKey = $request->input('_deleted_feature_key');
        $deletedPackageId  = $request->input('_deleted_package_id');

        if (! $deletedFeatureKey || ! $deletedPackageId) {
            Log::error('DELETE sync failed: missing deleted feature data', [
                'deleted_feature_key' => $deletedFeatureKey,
                'deleted_package_id'  => $deletedPackageId,
            ]);
            return;
        }

        $tenants = ModelsTenant::whereHas('subscription', function ($query) use ($deletedPackageId) {
            $query->where('package_id', $deletedPackageId)
                ->where('active', true);
        })->get();

        foreach ($tenants as $tenant) {
            try {
                $this->connectToTenant($tenant);
            } catch (Throwable $e) {
                Log::error('Failed to connect to tenant DB for DELETE', [
                    'tenant_id' => $tenant->id,
                    'message'   => $e->getMessage(),
                ]);
                continue;
            }

            try {
                DB::connection('tenant')
                    ->table('tenant_feature_usage')
                    ->where('feature_slug', $deletedFeatureKey)
                    ->delete();
            } catch (Throwable $e) {
                Log::error('Failed to delete feature for tenant', [
                    'tenant_id' => $tenant->id,
                    'message'   => $e->getMessage(),
                ]);
                continue;
            }
        }
    }

    private function connectToTenant(object $tenant): void
    {
        Config::set('database.connections.tenant.driver',   'pgsql');
        Config::set('database.connections.tenant.host',     $tenant->db_host);
        Config::set('database.connections.tenant.port',     5432);
        Config::set('database.connections.tenant.database', $tenant->db_name);
        Config::set('database.connections.tenant.username', $tenant->db_user);
        Config::set('database.connections.tenant.password', $tenant->db_pass);
        Config::set('database.connections.tenant.charset',  'utf8');

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function syncTenantFeatures(\Illuminate\Support\Collection $featuresMap, array $currentKeys): void
    {
        foreach ($featuresMap as $slug => $data) {

            $exists = DB::connection('tenant')
                ->table('tenant_feature_usage')
                ->where('feature_slug', $slug)
                ->exists();

            if ($exists) {
                DB::connection('tenant')
                    ->table('tenant_feature_usage')
                    ->where('feature_slug', $slug)
                    ->update([
                        'total_limit' => $data['total_limit'],
                        'type'        => $data['type'],
                        'status'      => $data['status'],
                        'updated_at'  => now(),
                    ]);
            } else {
                DB::connection('tenant')
                    ->table('tenant_feature_usage')
                    ->insert([
                        'feature_slug' => $slug,
                        'total_limit'  => $data['total_limit'],
                        'used_amount'  => 0,
                        'type'         => $data['type'],
                        'is_enabled'   => true,
                        'status'       => $data['status'],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
            }
        }

        DB::connection('tenant')
            ->table('tenant_feature_usage')
            ->whereNotIn('feature_slug', $currentKeys)
            ->delete();
    }

    private function shouldSync(Request $request, Response $response): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])
            && str_contains($request->path(), 'feature_packages')
            && $response->isSuccessful();
    }
}
