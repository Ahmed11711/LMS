<?php

namespace App\Http\Requests\Admin\Subject;
use App\Http\Requests\BaseRequest\BaseRequest;
class SubjectUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'grade_id' => 'sometimes|required|integer|exists:grades,id',
        ];
    }
}
