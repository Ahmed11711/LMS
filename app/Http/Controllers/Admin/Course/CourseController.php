<?php

namespace App\Http\Controllers\Admin\Course;

use App\Repositories\Course\CourseRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Course\CourseStoreRequest;
use App\Http\Requests\Admin\Course\CourseUpdateRequest;
use App\Http\Resources\Admin\Course\CourseResource;

class CourseController extends BaseController
{
    public function __construct(CourseRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Course',
            fileFields: ['image']


        );
        $this->withRelationships = ['chapters.lessons'];
        $this->storeRequestClass = CourseStoreRequest::class;
        $this->updateRequestClass = CourseUpdateRequest::class;
        $this->resourceClass = CourseResource::class;
    }
}
