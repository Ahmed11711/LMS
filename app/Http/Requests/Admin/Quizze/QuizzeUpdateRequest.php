<?php

namespace App\Http\Requests\Admin\Quizze;
use App\Http\Requests\BaseRequest\BaseRequest;
class QuizzeUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|sometimes|string',
        ];
    }
}
