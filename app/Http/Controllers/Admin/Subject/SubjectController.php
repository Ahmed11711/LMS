<?php

namespace App\Http\Controllers\Admin\Subject;

use App\Repositories\Subject\SubjectRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Subject\SubjectStoreRequest;
use App\Http\Requests\Admin\Subject\SubjectUpdateRequest;
use App\Http\Resources\Admin\Subject\SubjectResource;

class SubjectController extends BaseController
{
    public function __construct(SubjectRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Subject'
        );

        $this->storeRequestClass = SubjectStoreRequest::class;
        $this->updateRequestClass = SubjectUpdateRequest::class;
        $this->resourceClass = SubjectResource::class;
    }
}
