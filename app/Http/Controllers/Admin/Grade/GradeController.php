<?php

namespace App\Http\Controllers\Admin\Grade;

use App\Repositories\Grade\GradeRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Grade\GradeStoreRequest;
use App\Http\Requests\Admin\Grade\GradeUpdateRequest;
use App\Http\Resources\Admin\Grade\GradeResource;

class GradeController extends BaseController
{
    public function __construct(GradeRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Grade'
        );

        $this->storeRequestClass = GradeStoreRequest::class;
        $this->updateRequestClass = GradeUpdateRequest::class;
        $this->resourceClass = GradeResource::class;
    }
}
