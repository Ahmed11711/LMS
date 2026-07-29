<?php

namespace App\Repositories\AcademicYear;

use App\Repositories\AcademicYear\AcademicYearRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\AcademicYear;

class AcademicYearRepository extends BaseRepository implements AcademicYearRepositoryInterface
{
    public function __construct(AcademicYear $model)
    {
        parent::__construct($model);
    }
}
