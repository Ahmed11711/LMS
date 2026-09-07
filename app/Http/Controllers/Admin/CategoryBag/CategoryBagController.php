<?php

namespace App\Http\Controllers\Admin\CategoryBag;

use App\Repositories\CategoryBag\CategoryBagRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\CategoryBag\CategoryBagStoreRequest;
use App\Http\Requests\Admin\CategoryBag\CategoryBagUpdateRequest;
use App\Http\Resources\Admin\CategoryBag\CategoryBagResource;

class CategoryBagController extends BaseController
{
    public function __construct(CategoryBagRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'CategoryBag'
        );

        $this->storeRequestClass = CategoryBagStoreRequest::class;
        $this->updateRequestClass = CategoryBagUpdateRequest::class;
        $this->resourceClass = CategoryBagResource::class;
    }
}
