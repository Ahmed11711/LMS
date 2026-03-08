<?php

namespace App\Http\Requests\Admin\Chapter;
use App\Http\Requests\BaseRequest\BaseRequest;
class ChapterStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
        ];
    }
}
