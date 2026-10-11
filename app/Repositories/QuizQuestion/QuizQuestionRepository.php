<?php

namespace App\Repositories\QuizQuestion;

use App\Repositories\QuizQuestion\QuizQuestionRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\QuizQuestion;

class QuizQuestionRepository extends BaseRepository implements QuizQuestionRepositoryInterface
{
    public function __construct(QuizQuestion $model)
    {
        parent::__construct($model);
    }
}
