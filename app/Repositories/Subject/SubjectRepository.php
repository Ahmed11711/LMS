<?php

namespace App\Repositories\Subject;

use App\Repositories\Subject\SubjectRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Subject;

class SubjectRepository extends BaseRepository implements SubjectRepositoryInterface
{
    public function __construct(Subject $model)
    {
        parent::__construct($model);
    }
}
