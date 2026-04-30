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
            'video_url' => collect([
                'https://www.w3schools.com/html/mov_bbb.mp4',
                'https://www.w3schools.com/html/movie.mp4',
                'https://samplelib.com/lib/preview/mp4/sample-5s.mp4',
            ])->random(),
            'library_id' => $this->library_id,
            'description' => $this->description,
            'order' => $this->order,
            'file_size_mb' => $this->file_size_mb,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
