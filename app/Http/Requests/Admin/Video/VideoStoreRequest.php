<?php

namespace App\Http\Requests\Admin\Video;
use App\Http\Requests\BaseRequest\BaseRequest;
class VideoStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'title' => 'required|string|max:255',
            'video_id' => 'required|string|max:255|exists:videos,id',
            'video_url' => 'required|string|max:255',
            'library_id' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
            'file_size_mb' => 'required|numeric|file|max:2048',
        ];
    }
}
