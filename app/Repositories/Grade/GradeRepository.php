<?php

namespace App\Repositories\Grade;

use App\Repositories\Grade\GradeRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Grade;

class GradeRepository extends BaseRepository implements GradeRepositoryInterface
{
    public function __construct(Grade $model)
    {
        parent::__construct($model);
    }
}
