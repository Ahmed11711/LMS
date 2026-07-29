<?php

namespace App\Http\Requests\Admin\Template;
use App\Http\Requests\BaseRequest\BaseRequest;
class TemplateStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'content' => 'nullable|array',
            'course_id' => 'nullable|integer|exists:courses,id',
            'is_active' => 'required|boolean',
        ];
    }
}
