<?php

namespace App\Http\Requests\Admin\Quizze;

use App\Http\Requests\BaseRequest\BaseRequest;

/** PUT /quizzes/{quizId}/questions : new order of the questions already attached to the quiz. */
class QuizzeQuestionReorderRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_ids'   => ['required', 'array', 'min:1', 'max:200'],
            'question_ids.*' => ['integer', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_ids.required'   => 'ابعت ترتيب الأسئلة.',
            'question_ids.array'      => 'question_ids لازم تكون قائمة أرقام.',
            'question_ids.*.integer'  => 'رقم السؤال لازم يكون رقم صحيح.',
            'question_ids.*.distinct' => 'في سؤال متكرر.',
        ];
    }
}
