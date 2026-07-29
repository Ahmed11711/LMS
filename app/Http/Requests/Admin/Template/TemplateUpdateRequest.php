<?php

namespace App\Http\Requests\Admin\Template;
use App\Http\Requests\BaseRequest\BaseRequest;
class TemplateUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|sometimes|integer|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
            'content' => 'nullable|sometimes|array',
            'course_id' => 'nullable|sometimes|integer|exists:courses,id',
            'is_active' => 'sometimes|required|boolean',
        ];
    }
}
