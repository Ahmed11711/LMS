<?php

namespace App\Http\Requests\Admin\Lesson;
use App\Http\Requests\BaseRequest\BaseRequest;
class LessonUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chapter_id' => 'sometimes|required|integer|exists:chapters,id',
            'title' => 'sometimes|required|string|max:255',
            'video_id' => 'sometimes|required|string|max:255',
            'library_id' => 'sometimes|required|string|max:255',
            'description' => 'nullable|sometimes|string',
            'order' => 'sometimes|required|integer',
        ];
    }
}
