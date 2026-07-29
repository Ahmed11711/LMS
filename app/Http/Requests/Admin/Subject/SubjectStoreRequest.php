<?php

namespace App\Http\Requests\Admin\Subject;
use App\Http\Requests\BaseRequest\BaseRequest;
class SubjectStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'grade_id' => 'required|integer|exists:grades,id',
        ];
    }
}
