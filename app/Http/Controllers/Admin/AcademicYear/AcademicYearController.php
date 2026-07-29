<?php

namespace App\Http\Controllers\Admin\AcademicYear;

use App\Repositories\AcademicYear\AcademicYearRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\AcademicYear\AcademicYearStoreRequest;
use App\Http\Requests\Admin\AcademicYear\AcademicYearUpdateRequest;
use App\Http\Resources\Admin\AcademicYear\AcademicYearResource;

class AcademicYearController extends BaseController
{
    public function __construct(AcademicYearRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'AcademicYear'
        );

        $this->storeRequestClass = AcademicYearStoreRequest::class;
        $this->updateRequestClass = AcademicYearUpdateRequest::class;
        $this->resourceClass = AcademicYearResource::class;
    }
}
