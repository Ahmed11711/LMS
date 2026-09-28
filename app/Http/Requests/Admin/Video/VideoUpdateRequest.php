<?php

namespace App\Http\Requests\Admin\Video;
use App\Http\Requests\BaseRequest\BaseRequest;
class VideoUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'title' => 'sometimes|required|string|max:255',
            'video_id' => 'sometimes|required|string|max:255|exists:videos,id',
            'video_url' => 'sometimes|required|string|max:255',
            'library_id' => 'sometimes|required|string|max:255',
            'description' => 'nullable|sometimes|string',
            'order' => 'sometimes|required|integer',
            'file_size_mb' => 'sometimes|required|numeric|file|max:2048',
        ];
    }
}
