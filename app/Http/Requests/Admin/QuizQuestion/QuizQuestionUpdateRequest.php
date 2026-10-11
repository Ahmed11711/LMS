<?php

namespace App\Http\Requests\Admin\QuizQuestion;

/**
 * Partial update. Send answers (or correct_answer / meta.numeric) only when you want
 * them replaced; if the type changes they become mandatory.
 * Multipart note: PHP does not parse multipart on PUT, so send POST + _method=PUT.
 */
class QuizQuestionUpdateRequest extends QuizQuestionRequest
{
    protected function isStore(): bool
    {
        return false;
    }
}
