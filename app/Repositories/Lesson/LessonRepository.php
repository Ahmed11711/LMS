<?php

namespace App\Repositories\Lesson;

use App\Repositories\Lesson\LessonRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Lesson;

class LessonRepository extends BaseRepository implements LessonRepositoryInterface
{
    public function __construct(Lesson $model)
    {
        parent::__construct($model);
    }

    public function myNots($userId, $lesonId) {}

    public function allCommand($lesonId) {}
}
