<?php

namespace App\Repositories\Lesson;

use App\Repositories\BaseRepository\BaseRepositoryInterface;

interface LessonRepositoryInterface extends BaseRepositoryInterface
{
    public function myNots($userId, $lesonId);
}
