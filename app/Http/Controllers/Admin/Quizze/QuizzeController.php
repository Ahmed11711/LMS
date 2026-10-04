<?php

namespace App\Http\Controllers\Admin\Quizze;

use App\Repositories\Quizze\QuizzeRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Quizze\QuizzeStoreRequest;
use App\Http\Requests\Admin\Quizze\QuizzeUpdateRequest;
use App\Http\Resources\Admin\Quizze\QuizzeResource;

class QuizzeController extends BaseController
{
    public function __construct(QuizzeRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Quizze'
        );

        $this->storeRequestClass = QuizzeStoreRequest::class;
        $this->updateRequestClass = QuizzeUpdateRequest::class;
        $this->resourceClass = QuizzeResource::class;
    }
}
