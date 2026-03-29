<?php

namespace App\Http\Controllers\Admin\FeaturePackage;

use App\Repositories\FeaturePackage\FeaturePackageRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\FeaturePackage\FeaturePackageStoreRequest;
use App\Http\Requests\Admin\FeaturePackage\FeaturePackageUpdateRequest;
use App\Http\Resources\Admin\FeaturePackage\FeaturePackageResource;

class FeaturePackageController extends BaseController
{
    public function __construct(FeaturePackageRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'FeaturePackage'
        );

        $this->storeRequestClass = FeaturePackageStoreRequest::class;
        $this->updateRequestClass = FeaturePackageUpdateRequest::class;
        $this->resourceClass = FeaturePackageResource::class;
    }
}
