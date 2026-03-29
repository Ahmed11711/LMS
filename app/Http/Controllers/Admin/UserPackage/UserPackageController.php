<?php

namespace App\Http\Controllers\Admin\UserPackage;

use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserPackage\UserPackageStoreRequest;
use App\Http\Requests\Admin\UserPackage\UserPackageUpdateRequest;
use App\Http\Resources\Admin\UserPackage\UserPackageResource;

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
}
