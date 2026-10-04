<?php

namespace App\Repositories\Quizze;

use App\Repositories\Quizze\QuizzeRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Quizze;

class QuizzeRepository extends BaseRepository implements QuizzeRepositoryInterface
{
    public function __construct(Quizze $model)
    {
        parent::__construct($model);
    }
}
