<?php

namespace App\Http\Controllers\Admin\Lesson;

use App\Repositories\Lesson\LessonRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Lesson\LessonStoreRequest;
use App\Http\Requests\Admin\Lesson\LessonUpdateRequest;
use App\Http\Resources\Admin\Lesson\LessonResource;

class LessonController extends BaseController
{
    public function __construct(LessonRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Lesson'
        );

        $this->storeRequestClass = LessonStoreRequest::class;
        $this->updateRequestClass = LessonUpdateRequest::class;
        $this->resourceClass = LessonResource::class;
    }
}
