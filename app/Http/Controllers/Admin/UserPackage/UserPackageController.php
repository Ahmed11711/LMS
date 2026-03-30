<?php

namespace App\Http\Controllers\Admin\UserPackage;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserPackage\UserPackageStoreRequest;
use App\Http\Requests\Admin\UserPackage\UserPackageUpdateRequest;
use App\Http\Resources\Admin\UserPackage\UserPackageResource;
use App\Models\UserPackage;
use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Request;

class UserPackageController extends BaseController
{
    public function __construct(UserPackageRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'UserPackage'
        );

        $this->storeRequestClass = UserPackageStoreRequest::class;
        $this->updateRequestClass = UserPackageUpdateRequest::class;
        $this->resourceClass = UserPackageResource::class;
    }

    public function test(Request $request)
    {
        $userId = $request->get('user_id');
        $tenantPackage = UserPackage::where('user_id', $userId)->first();

        if (!$tenantPackage) {
            return response()->json(['message' => 'No active package'], 404);
        }

        $packageDetails = DB::connection('LMS_CENTER')
            ->table('packages')
            ->where('id', $tenantPackage->package_id)
            ->first();

        return response()->json([
            'package_info' => $tenantPackage,
            'features' => $packageDetails
        ]);
    }
}
