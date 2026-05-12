<?php

namespace App\Http\Resources\User\Lesson;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyLessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    // MyLessonResource
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'video_id'        => $this->video_id,
            'video_url'       => $this->video_url,
            'order'           => $this->order,
            'is_free'         => $this->is_free,
            'watched_seconds' => $this->progresses->first()?->watched_seconds ?? 0,
            'is_completed'    => (bool) $this->progresses->first()?->is_completed ?? false,
        ];
    }
}
