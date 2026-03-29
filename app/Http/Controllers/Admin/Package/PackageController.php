<?php

namespace App\Http\Controllers\Admin\Package;

use App\Repositories\Package\PackageRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Package\PackageStoreRequest;
use App\Http\Requests\Admin\Package\PackageUpdateRequest;
use App\Http\Resources\Admin\Package\PackageResource;

class PackageController extends BaseController
{
    public function __construct(PackageRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Package'
        );

        $this->storeRequestClass = PackageStoreRequest::class;
        $this->updateRequestClass = PackageUpdateRequest::class;
        $this->resourceClass = PackageResource::class;
    }
}
