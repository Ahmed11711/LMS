<?php

namespace App\Http\Controllers\Admin\UserPackage;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserPackage\UpgradeRequestStoreRequest;
use App\Http\Requests\Admin\UserPackage\UserPackageStoreRequest;
use App\Http\Requests\Admin\UserPackage\UserPackageUpdateRequest;
use App\Http\Resources\Admin\UserPackage\UserPackageResource;
use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserPackageController extends BaseController
{
    public function __construct(UserPackageRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;

        $this->initService(
            repository: $repository,
            collectionName: 'UserPackage'
        );

        $this->storeRequestClass = UserPackageStoreRequest::class;
        $this->updateRequestClass = UserPackageUpdateRequest::class;
        $this->resourceClass = UserPackageResource::class;
    }
    public function myPacake(UserPackageRepositoryInterface $repository, Request $request)
    {
        $userId = $request->get('user_id');

        $myPackage = $repository->MyPackageWithStatus($userId);

        if (!$myPackage) {
            return response()->json(['message' => 'No active package'], 404);
        }

        $packageDetails = DB::connection('LMS_CENTER')
            ->table('feature_packages')
            ->join('features', 'features.id', '=', 'feature_packages.feature_id')
            ->where('feature_packages.package_id', $myPackage->package_id)
            ->select(
                'feature_packages.id',
                'feature_packages.package_id',
                'feature_packages.feature_id',
                'feature_packages.value',
                'features.label',
                'features.key as key_feature'
            )
            ->get();

        return response()->json([
            'package_info' => $myPackage,
            'features' => $packageDetails
        ]);
    }
    /**
     * الأكاديمية بتطلب ترقية: بيتعمل صف جديد في user_packages بحالة pending
     */
    public function requestUpgrade(UpgradeRequestStoreRequest $request, UserPackageRepositoryInterface $repository)
    {
        $user = Auth::user();
        $userId = Auth::user()->id;

        $newPackage = DB::connection('LMS_CENTER')->table('packages')
            ->where('id', $request->input('package_id'))
            ->first();

        if (!$newPackage) {
            return response()->json(['message' => 'الباكدج المطلوبة غير موجودة'], 404);
        }

        $path = $request->file('payment_proof')->store('upgrade-requests', 'public');

        // البيانات المشتركة بين الاتنين
        $baseData = [
            'package_id'    => $newPackage->id,
            'package_name'  => $newPackage->titile,
            'status'        => 'pending',
            'active'        => false,
            'price'         => $newPackage->price ?? 0,
            'payment_proof' => $path,
        ];

        $tenantData = array_merge($baseData, [
            'user_id' => $user->id,
        ]);

        $centralData = array_merge($baseData, [
            'user_id' => $user->academy_id,
        ]);

        $pendingRequest = null;
        $centralInserted = false;

        try {
            $repository->cancelPendingRequests($user->id, $user->academy_id);

            $pendingRequest = $repository->createPendingUpgrade($tenantData);

            // تخزين في قاعدة البيانات المركزية (Central)
            DB::connection('LMS_CENTER')->table('user_packages')->insert(
                array_merge($centralData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
            $centralInserted = true;
        } catch (\Throwable $e) {

            if ($pendingRequest && !$centralInserted) {
                try {
                    $repository->delete($pendingRequest->id ?? $pendingRequest['id']);
                } catch (\Throwable $rollbackException) {
                    Log::error('Failed to rollback tenant pending upgrade after central insert failure', [
                        'user_id' => $userId,
                        'message' => $rollbackException->getMessage(),
                    ]);
                }
            }

            Log::error('Failed to create upgrade request', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'message' => 'حدث خطأ أثناء إرسال طلب الترقية، برجاء المحاولة مرة أخرى',
            ], 500);
        }

        return response()->json([
            'message' => 'تم إرسال طلب الترقية، بانتظار موافقة الأدمن',
            'data'    => $pendingRequest,
        ]);
    }
    // public function approveUpgrade(int $userPackageId, UserPackageRepositoryInterface $repository)
    // {
    //     Log::info('Approving upgrade request', ['user_package_id' => $userPackageId]);
    //     $pendingRequest = $repository->findPendingRequest($userPackageId);

    //     if (!$pendingRequest) {
    //         return response()->json(['message' => 'الطلب غير موجود أو تم التعامل معه من قبل'], 404);
    //     }

    //     $packageInfo = DB::connection('LMS_CENTER')->table('packages')
    //         ->where('id', $pendingRequest->package_id)
    //         ->first();

    //     $durationDays = (float) ($packageInfo->duration_months ?? 1) * 30;

    //     DB::connection('LMS_CENTER')->beginTransaction();

    //     try {
    //         $repository->expireActivePackage($pendingRequest->user_id);

    //         $pendingRequest->update([
    //             'status'      => 'active',
    //             'active'      => true,
    //             'start_date'  => now(),
    //             'end_date'    => now()->addDays($durationDays),
    //             'approved_at' => now(),
    //         ]);

    //         $features = DB::connection('LMS_CENTER')->table('feature_packages')
    //             ->where('package_id', $pendingRequest->package_id)
    //             ->whereNotNull('key_feature')
    //             ->where('key_feature', '!=', '')
    //             ->get();

    //         DB::connection('LMS_CENTER')->commit();

    //         $tenant = DB::connection('LMS_CENTER')->table('tenants')
    //             ->where('user_id', $pendingRequest->user_id)
    //             ->first();

    //         if ($tenant) {
    //             config([
    //                 'database.connections.tenant.driver'   => 'pgsql',
    //                 'database.connections.tenant.host'     => $tenant->db_host,
    //                 'database.connections.tenant.database' => $tenant->db_name,
    //                 'database.connections.tenant.username' => $tenant->db_user,
    //                 'database.connections.tenant.password' => $tenant->db_pass,
    //                 'database.connections.tenant.port'     => 5432,
    //             ]);
    //             DB::purge('tenant');
    //             DB::reconnect('tenant');

    //             $tenantLocalUser = DB::connection('tenant')->table('users')
    //                 ->where('academy_id', $pendingRequest->user_id)
    //                 ->first();

    //             if (!$tenantLocalUser) {
    //                 Log::error('Tenant local user not found for academy_id', [
    //                     'academy_id' => $pendingRequest->user_id,
    //                     'tenant_db'  => $tenant->db_name,
    //                 ]);

    //                 return response()->json([
    //                     'message' => 'تعذر إيجاد المستخدم المقابل داخل قاعدة بيانات الأكاديمية'
    //                 ], 404);
    //             }

    //             $tenantUserId = $tenantLocalUser->id;

    //             DB::connection('tenant')->table('user_packages')
    //                 ->where('status', 'active')
    //                 ->update(['active' => false, 'status' => 'expired']);

    //             DB::connection('tenant')->table('user_packages')->insert([
    //                 'user_id'      => $tenantUserId,
    //                 'package_id'   => $pendingRequest->package_id,
    //                 'package_name' => $pendingRequest->package_name,
    //                 'start_date'   => now(),
    //                 'end_date'     => now()->addDays($durationDays),
    //                 'active'       => true,
    //                 'status'       => 'active',
    //                 'price'        => $pendingRequest->price,
    //                 'created_at'   => now(),
    //             ]);

    //             foreach ($features as $f) {
    //                 Log::info('Processing feature for tenant', [
    //                     'feature_package_id' => $f->id,
    //                     'package_id'         => $f->package_id,
    //                     'feature_id'         => $f->feature_id,
    //                     'key_feature'        => $f->key_feature,
    //                     'value'              => $f->value,
    //                 ]);
    //                 if (empty($f->key_feature)) {
    //                     Log::warning('Feature package missing key_feature, skipped', [
    //                         'feature_package_id' => $f->id,
    //                         'package_id'          => $f->package_id,
    //                         'feature_id'          => $f->feature_id,
    //                     ]);
    //                     continue;
    //                 }

    //                 DB::connection('tenant')->table('tenant_feature_usage')->updateOrInsert(
    //                     ['feature_slug' => $f->key_feature],
    //                     [
    //                         'total_limit' => $f->value,
    //                         'used_amount' => 0,
    //                         'type'        => ($f->value == -1 || (int) $f->value > 1) ? 'numeric' : 'boolean',
    //                         'is_enabled'  => $f->value != 0,
    //                         'updated_at'  => now(),
    //                     ]
    //                 );
    //             }
    //         }

    //         return response()->json([
    //             'message' => 'تمت الترقية بنجاح',
    //             'data'    => $pendingRequest,
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::connection('LMS_CENTER')->rollBack();
    //         return response()->json(['message' => $e->getMessage()], 400);
    //     }
    // }

    public function approveUpgrade(int $userPackageId, UserPackageRepositoryInterface $repository)
    {
        Log::info('[approveUpgrade] 1. Start', ['user_package_id' => $userPackageId]);

        $pendingRequest = $repository->findPendingRequest($userPackageId);

        if (!$pendingRequest) {
            Log::warning('[approveUpgrade] 2. Pending request NOT found -> 404', [
                'user_package_id' => $userPackageId,
            ]);
            return response()->json(['message' => 'الطلب غير موجود أو تم التعامل معه من قبل'], 404);
        }

        Log::info('[approveUpgrade] 2. Pending request found', [
            'id'           => $pendingRequest->id ?? null,
            'user_id'      => $pendingRequest->user_id,
            'package_id'   => $pendingRequest->package_id,
            'package_name' => $pendingRequest->package_name,
            'status'       => $pendingRequest->status ?? null,
        ]);

        $packageInfo = DB::connection('LMS_CENTER')->table('packages')
            ->where('id', $pendingRequest->package_id)
            ->first();

        $durationDays = (float) ($packageInfo->duration_months ?? 1) * 30;

        Log::info('[approveUpgrade] 3. Package info', [
            'package_found'   => (bool) $packageInfo,
            'duration_months' => $packageInfo->duration_months ?? null,
            'duration_days'   => $durationDays,
        ]);

        DB::connection('LMS_CENTER')->beginTransaction();

        try {
            $repository->expireActivePackage($pendingRequest->user_id);
            Log::info('[approveUpgrade] 4. Old active package expired', [
                'user_id' => $pendingRequest->user_id,
            ]);

            $pendingRequest->update([
                'status'      => 'active',
                'active'      => true,
                'start_date'  => now(),
                'end_date'    => now()->addDays($durationDays),
                'approved_at' => now(),
            ]);
            Log::info('[approveUpgrade] 5. Pending request updated to active');

            // (debug) كل الـ features للباقة من غير أي فلتر، عشان نشوف الفلتر بيشيل إيه
            $allFeatures = DB::connection('LMS_CENTER')->table('feature_packages')
                ->where('package_id', $pendingRequest->package_id)
                ->get();

            Log::info('[approveUpgrade] 6a. ALL feature_packages (no filter)', [
                'package_id' => $pendingRequest->package_id,
                'count'      => $allFeatures->count(),
                'rows'       => $allFeatures->map(fn($r) => [
                    'id'          => $r->id,
                    'feature_id'  => $r->feature_id ?? null,
                    'key_feature' => $r->key_feature ?? null,
                    'value'       => $r->value ?? null,
                ])->values()->all(),
            ]);

            $features = DB::connection('LMS_CENTER')->table('feature_packages')
                ->where('package_id', $pendingRequest->package_id)
                ->whereNotNull('key_feature')
                ->where('key_feature', '!=', '')
                ->get();

            Log::info('[approveUpgrade] 6b. FILTERED features', [
                'package_id' => $pendingRequest->package_id,
                'count'      => $features->count(),
            ]);

            DB::connection('LMS_CENTER')->commit();
            Log::info('[approveUpgrade] 7. LMS_CENTER transaction committed');

            $tenant = DB::connection('LMS_CENTER')->table('tenants')
                ->where('user_id', $pendingRequest->user_id)
                ->first();

            Log::info('[approveUpgrade] 8. Tenant lookup', [
                'user_id'      => $pendingRequest->user_id,
                'tenant_found' => (bool) $tenant,
                'db_host'      => $tenant->db_host ?? null,
                'db_name'      => $tenant->db_name ?? null,
            ]);

            if (!$tenant) {
                Log::warning('[approveUpgrade] 8. No tenant -> skipping tenant sync and features loop');
            }

            if ($tenant) {
                config([
                    'database.connections.tenant.driver'   => 'pgsql',
                    'database.connections.tenant.host'     => $tenant->db_host,
                    'database.connections.tenant.database' => $tenant->db_name,
                    'database.connections.tenant.username' => $tenant->db_user,
                    'database.connections.tenant.password' => $tenant->db_pass,
                    'database.connections.tenant.port'     => 5432,
                ]);
                DB::purge('tenant');
                DB::reconnect('tenant');
                Log::info('[approveUpgrade] 9. Tenant connection configured', [
                    'db_name' => $tenant->db_name,
                ]);

                $tenantLocalUser = DB::connection('tenant')->table('users')
                    ->where('academy_id', $pendingRequest->user_id)
                    ->first();

                Log::info('[approveUpgrade] 10. Tenant local user lookup', [
                    'academy_id' => $pendingRequest->user_id,
                    'found'      => (bool) $tenantLocalUser,
                    'local_id'   => $tenantLocalUser->id ?? null,
                ]);

                if (!$tenantLocalUser) {
                    Log::error('[approveUpgrade] 10. Tenant local user not found -> 404', [
                        'academy_id' => $pendingRequest->user_id,
                        'tenant_db'  => $tenant->db_name,
                    ]);

                    return response()->json([
                        'message' => 'تعذر إيجاد المستخدم المقابل داخل قاعدة بيانات الأكاديمية'
                    ], 404);
                }

                $tenantUserId = $tenantLocalUser->id;

                $expiredCount = DB::connection('tenant')->table('user_packages')
                    ->where('status', 'active')
                    ->update(['active' => false, 'status' => 'expired']);

                Log::info('[approveUpgrade] 11. Tenant user_packages expired', [
                    'affected_rows' => $expiredCount,
                ]);

                DB::connection('tenant')->table('user_packages')->insert([
                    'user_id'      => $tenantUserId,
                    'package_id'   => $pendingRequest->package_id,
                    'package_name' => $pendingRequest->package_name,
                    'start_date'   => now(),
                    'end_date'     => now()->addDays($durationDays),
                    'active'       => true,
                    'status'       => 'active',
                    'price'        => $pendingRequest->price,
                    'created_at'   => now(),
                ]);
                Log::info('[approveUpgrade] 12. Tenant user_packages inserted', [
                    'tenant_user_id' => $tenantUserId,
                    'package_id'     => $pendingRequest->package_id,
                ]);

                Log::info('[approveUpgrade] 13. Before features loop', [
                    'features_count' => $features->count(),
                ]);

                foreach ($features as $f) {
                    Log::info('[approveUpgrade] 14. Processing feature for tenant', [
                        'feature_package_id' => $f->id,
                        'package_id'         => $f->package_id,
                        'feature_id'         => $f->feature_id,
                        'key_feature'        => $f->key_feature,
                        'value'              => $f->value,
                    ]);

                    if (empty($f->key_feature)) {
                        Log::warning('[approveUpgrade] 14. Feature missing key_feature, skipped', [
                            'feature_package_id' => $f->id,
                            'package_id'         => $f->package_id,
                            'feature_id'         => $f->feature_id,
                        ]);
                        continue;
                    }

                    $result = DB::connection('tenant')->table('tenant_feature_usage')->updateOrInsert(
                        ['feature_slug' => $f->key_feature],
                        [
                            'total_limit' => $f->value,
                            'used_amount' => 0,
                            'type'        => ($f->value == -1 || (int) $f->value > 1) ? 'numeric' : 'boolean',
                            'is_enabled'  => $f->value != 0,
                            'updated_at'  => now(),
                        ]
                    );

                    Log::info('[approveUpgrade] 15. tenant_feature_usage updateOrInsert done', [
                        'feature_slug' => $f->key_feature,
                        'result'       => $result,
                    ]);
                }

                $usageCount = DB::connection('tenant')->table('tenant_feature_usage')->count();
                Log::info('[approveUpgrade] 16. After loop, tenant_feature_usage rows', [
                    'count' => $usageCount,
                ]);
            }

            Log::info('[approveUpgrade] 17. Done successfully');

            return response()->json([
                'message' => 'تمت الترقية بنجاح',
                'data'    => $pendingRequest,
            ]);
        } catch (\Throwable $e) {
            DB::connection('LMS_CENTER')->rollBack();

            Log::error('[approveUpgrade] EXCEPTION', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => Str::limit($e->getTraceAsString(), 1500),
            ]);

            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
