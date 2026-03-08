<?php

namespace App\Repositories\Course;

use App\Repositories\Course\CourseRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Course;

class CourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }
}
