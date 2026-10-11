<?php

namespace App\Http\Requests\Admin\QuizQuestion;

class QuizQuestionStoreRequest extends QuizQuestionRequest
{
    protected function isStore(): bool
    {
        return true;
    }
}
