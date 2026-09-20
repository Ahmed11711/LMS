<?php

namespace App\Http\Resources\Admin\Chapter;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Admin\Lesson\LessonResource;
use App\Http\Resources\Admin\OnlineSession\OnlineSessionResource;
use App\Http\Resources\Admin\PhysicalCourseDetail\PhysicalCourseDetailResource;

class ChapterResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'order' => $this->order,
            'lessons' => $this->resolveLessons(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function resolveLessons()
    {
        $type = $this->relationLoaded('course') ? $this->course?->type : null;

        return match ($type) {
            'online'  => OnlineSessionResource::collection($this->whenLoaded('onlineSessions')),
            'offline' => PhysicalCourseDetailResource::collection($this->whenLoaded('physicalCourseDetails')),
            default   => LessonResource::collection($this->whenLoaded('lessons')),
        };
    }
}
