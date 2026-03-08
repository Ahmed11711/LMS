<?php

namespace App\Http\Requests\Admin\Lesson;
use App\Http\Requests\BaseRequest\BaseRequest;
class LessonStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chapter_id' => 'required|integer|exists:chapters,id',
            'title' => 'required|string|max:255',
            'video_id' => 'required|string|max:255',
            'library_id' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
        ];
    }
}
