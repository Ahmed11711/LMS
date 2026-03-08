<?php

namespace App\Http\Requests\Admin\Chapter;
use App\Http\Requests\BaseRequest\BaseRequest;
class ChapterUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'sometimes|required|integer|exists:courses,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|sometimes|string',
            'order' => 'sometimes|required|integer',
        ];
    }
}
