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

        $myPackage = $repository->MyPackage($userId);

        if (!$myPackage) {
            return response()->json(['message' => 'No active package'], 404);
        }

        $packageDetails = DB::connection('LMS_CENTER')
            ->table('feature_packages')
            ->where('package_id', $myPackage->package_id)
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
        $userId = Auth::id();

        if ($repository->hasPendingRequest($userId)) {
            return response()->json(['message' => 'لديك طلب ترقية معلق بالفعل، برجاء انتظار الرد'], 422);
        }

        $newPackage = DB::connection('LMS_CENTER')->table('packages')
            ->where('id', $request->input('package_id'))
            ->first();

        if (!$newPackage) {
            return response()->json(['message' => 'الباكدج المطلوبة غير موجودة'], 404);
        }

        $path = $request->file('payment_proof')->store('upgrade-requests', 'public');

        $pendingRequest = $repository->createPendingUpgrade([
            'user_id'       => $userId,
            'package_id'    => $newPackage->id,
            'package_name'  => $newPackage->titile, // ✅ الاسم الصح
            'status'        => 'pending',
            'active'        => false,
            'price'         => $newPackage->price ?? 0,
            'payment_proof' => $path,
            'amount'        => $request->input('amount'),
        ]);

        return response()->json([
            'message' => 'تم إرسال طلب الترقية، بانتظار موافقة الأدمن',
            'data'    => $pendingRequest,
        ]);
    }
    /**
     * السوبر أدمن بيوافق: بيتفعل الصف اللي كان pending ويقفل القديم
     */
    public function approveUpgrade(int $userPackageId, UserPackageRepositoryInterface $repository)
    {
        $pendingRequest = $repository->findPendingRequest($userPackageId);

        if (!$pendingRequest) {
            return response()->json(['message' => 'الطلب غير موجود أو تم التعامل معه من قبل'], 404);
        }

        $packageInfo = DB::connection('LMS_CENTER')->table('packages')
            ->where('id', $pendingRequest->package_id)
            ->first();

        $durationDays = (float) ($packageInfo->duration_months ?? 1) * 30;

        DB::connection('LMS_CENTER')->beginTransaction();

        try {
            $repository->expireActivePackage($pendingRequest->user_id);

            $pendingRequest->update([
                'status'      => 'active',
                'active'      => true,
                'start_date'  => now(),
                'end_date'    => now()->addDays($durationDays),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            $features = DB::connection('LMS_CENTER')->table('feature_packages')
                ->where('package_id', $pendingRequest->package_id)
                ->get();

            DB::connection('LMS_CENTER')->commit();

            $tenant = DB::connection('LMS_CENTER')->table('tenants')
                ->where('user_id', $pendingRequest->user_id)
                ->first();

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

                DB::connection('tenant')->table('user_packages')
                    ->where('status', 'active')
                    ->update(['active' => false, 'status' => 'expired']);

                DB::connection('tenant')->table('user_packages')->insert([
                    'user_id'      => $pendingRequest->user_id,
                    'package_id'   => $pendingRequest->package_id,
                    'package_name' => $pendingRequest->package_name,
                    'start_date'   => now(),
                    'end_date'     => now()->addDays($durationDays),
                    'active'       => true,
                    'status'       => 'active',
                    'price'        => $pendingRequest->price,
                    'created_at'   => now(),
                ]);

                foreach ($features as $f) {
                    DB::connection('tenant')->table('tenant_feature_usage')->updateOrInsert(
                        ['feature_slug' => $f->key_feature],
                        [
                            'total_limit' => $f->value,
                            'used_amount' => 0,
                            'type'        => ($f->value == -1 || (int)$f->value > 1) ? 'numeric' : 'boolean',
                            'is_enabled'  => $f->value != 0,
                            'updated_at'  => now(),
                        ]
                    );
                }
            }

            return response()->json([
                'message' => 'تمت الترقية بنجاح',
                'data'    => $pendingRequest,
            ]);
        } catch (\Exception $e) {
            DB::connection('LMS_CENTER')->rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
