<?php

namespace App\Http\Requests\Admin\QuizQuestion;


class QuizQuestionUpdateRequest extends QuizQuestionRequest
{
    protected function isStore(): bool
    {
        return false;
    }
}
