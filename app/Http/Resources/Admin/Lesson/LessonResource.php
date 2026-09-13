<?php

namespace App\Http\Resources\Admin\Lesson;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'chapter_id' => $this->chapter_id,
            'title' => $this->title,
            'video_id' => $this->video_id,
            'video_url' => $this->is_free ? $this->video_url : null,
            'library_id' => $this->library_id,
            'description' => $this->description,
            'order' => $this->order,
            'file_size_mb' => $this->file_size_mb,
            'is_free' => $this->is_free,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
