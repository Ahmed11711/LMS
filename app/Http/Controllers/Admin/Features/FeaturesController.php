<?php

namespace App\Http\Controllers\Admin\Features;

use App\Repositories\Features\FeaturesRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Features\FeaturesStoreRequest;
use App\Http\Requests\Admin\Features\FeaturesUpdateRequest;
use App\Http\Resources\Admin\Features\FeaturesResource;

class FeaturesController extends BaseController
{
    public function __construct(FeaturesRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Features'
        );

        $this->storeRequestClass = FeaturesStoreRequest::class;
        $this->updateRequestClass = FeaturesUpdateRequest::class;
        $this->resourceClass = FeaturesResource::class;
    }
}
