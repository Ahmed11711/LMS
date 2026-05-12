<?php

namespace App\Http\Resources\User\Chapter;

use App\Http\Resources\User\Lesson\MyLessonResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyChapterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    // MyChapterResource
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'title'   => $this->title,
            'order'   => $this->order,
            'lessons' => MyLessonResource::collection($this->lessons),
        ];
    }
}
