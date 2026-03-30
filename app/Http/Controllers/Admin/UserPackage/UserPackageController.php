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

        // Assign it to the property inherited from BaseController
        $this->repository = $repository;

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
        $myPackage = $this->repository->MyPackage($userId);
        if (!$myPackage) {
            return response()->json(['message' => 'No active package'], 404);
        }

        $packageDetails = DB::connection('LMS_CENTER')
            ->table('packages.packageFeatures')
            ->where('id', $myPackage->package_id)
            ->first();

        return response()->json([
            'package_info' => $myPackage,
            'features' => $packageDetails
        ]);
    }
}
