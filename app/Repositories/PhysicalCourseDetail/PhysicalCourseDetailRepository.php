<?php

namespace App\Repositories\PhysicalCourseDetail;

use App\Repositories\PhysicalCourseDetail\PhysicalCourseDetailRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\PhysicalCourseDetail;

class PhysicalCourseDetailRepository extends BaseRepository implements PhysicalCourseDetailRepositoryInterface
{
    public function __construct(PhysicalCourseDetail $model)
    {
        parent::__construct($model);
    }
}
