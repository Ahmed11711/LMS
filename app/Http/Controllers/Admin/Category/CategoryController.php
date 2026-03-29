<?php

namespace App\Http\Controllers\Admin\Category;

use App\Repositories\Category\CategoryRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Category\CategoryStoreRequest;
use App\Http\Requests\Admin\Category\CategoryUpdateRequest;
use App\Http\Resources\Admin\Category\CategoryResource;

class CategoryController extends BaseController
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Category'
        );

        $this->storeRequestClass = CategoryStoreRequest::class;
        $this->updateRequestClass = CategoryUpdateRequest::class;
        $this->resourceClass = CategoryResource::class;
    }
}
