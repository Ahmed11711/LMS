<?php

namespace App\Http\Requests\Admin\Quizze;

use App\Http\Requests\BaseRequest\BaseRequest;
use App\Models\QuizQuestion;
use Illuminate\Validation\Rule;

/** POST /quizzes/{quizId}/questions : add questions from the bank to a quiz. */
class QuizzeQuestionAttachRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_ids'   => ['required', 'array', 'min:1', 'max:200'],
            'question_ids.*' => [
                'integer',
                'distinct',
                Rule::exists(QuizQuestion::class, 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'question_ids.required'   => 'اختار سؤال واحد على الأقل.',
            'question_ids.array'      => 'question_ids لازم تكون قائمة أرقام.',
            'question_ids.min'        => 'اختار سؤال واحد على الأقل.',
            'question_ids.max'        => 'مينفعش تضيف أكتر من 200 سؤال مرة واحدة.',
            'question_ids.*.integer'  => 'رقم السؤال لازم يكون رقم صحيح.',
            'question_ids.*.exists'   => 'في سؤال مش موجود في البنك.',
            'question_ids.*.distinct' => 'في سؤال متكرر.',
        ];
    }
}
