<?php

namespace App\Http\Controllers\Admin\UserPackage;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserPackage\UserPackageStoreRequest;
use App\Http\Requests\Admin\UserPackage\UserPackageUpdateRequest;
use App\Http\Resources\Admin\UserPackage\UserPackageResource;
use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; // تأكد من استخدام Request الخاص بـ Laravel أفضل هنا

class UserPackageController extends BaseController
{
    // أزل كلمة public من هنا لمنع تعريف Property جديدة تتعارض مع الأب
    public function __construct(UserPackageRepositoryInterface $repository)
    {
        parent::__construct();

        // تعيين المستودع للخاصية الموجودة في BaseController
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
            ->table('package_features')
            ->where('id', $myPackage->package_id)
            ->first();

        return response()->json([
            'package_info' => $myPackage,
            'features' => $packageDetails
        ]);
    }
}
