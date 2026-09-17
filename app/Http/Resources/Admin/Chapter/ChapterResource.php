<?php

namespace App\Http\Resources\Admin\Chapter;

use App\Http\Resources\Admin\Lesson\LessonResource;

use App\Http\Resources\Admin\OnlineSession\OnlineSessionResource;
use App\Http\Resources\Admin\PhysicalCourseDetail\PhysicalCourseDetailResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
{
    // public function toArray($request): array
    // {
    //     return [
    //         'id' => $this->id,
    //         'course_id' => $this->course_id,
    //         'title' => $this->title,
    //         'description' => $this->description,
    //         'order' => $this->order,
    //         'lessons'       => LessonResource::collection($this->whenLoaded('lessons')),
    //         'created_at' => $this->created_at,
    //         'updated_at' => $this->updated_at,
    //     ];
    // }

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
        $type = $this->whenLoaded('course', fn() => $this->course->type);

        return match ($type) {
            'online'  => OnlineSessionResource::collection($this->whenLoaded('onlineSessions')),
            'offline' => PhysicalCourseDetailResource::collection($this->whenLoaded('physicalCourseDetails')),
            default   => LessonResource::collection($this->whenLoaded('lessons')), // recorded أو أي نوع تاني
        };
    }
}
