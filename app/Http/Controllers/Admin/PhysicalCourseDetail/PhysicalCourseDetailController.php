<?php

namespace App\Http\Controllers\Admin\PhysicalCourseDetail;

use App\Repositories\PhysicalCourseDetail\PhysicalCourseDetailRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\PhysicalCourseDetail\PhysicalCourseDetailStoreRequest;
use App\Http\Requests\Admin\PhysicalCourseDetail\PhysicalCourseDetailUpdateRequest;
use App\Http\Resources\Admin\PhysicalCourseDetail\PhysicalCourseDetailResource;

class PhysicalCourseDetailController extends BaseController
{
    public function __construct(PhysicalCourseDetailRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'PhysicalCourseDetail',
            fileFields: ['attachment']
        );

        $this->storeRequestClass = PhysicalCourseDetailStoreRequest::class;
        $this->updateRequestClass = PhysicalCourseDetailUpdateRequest::class;
        $this->resourceClass = PhysicalCourseDetailResource::class;
    }
}
