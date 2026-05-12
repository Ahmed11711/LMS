<?php

namespace App\Http\Resources\User\Course;

use App\Http\Resources\User\Chapter\MyChapterResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyCourseShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    // MyCourseShowResource
    public function toArray($request): array
    {
        return [
            'id'          => $this->course->id,
            'title'       => $this->course->title,
            'image'       => $this->course->image,
            'description' => $this->course->description,
            'chapters'    => MyChapterResource::collection($this->course->chapters),
        ];
    }
}
