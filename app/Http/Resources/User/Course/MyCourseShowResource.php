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
            'id'          => $this->id,
            'title'       => $this->title,
            'image'       => $this->image,
            'description' => $this->description,
            'chapters'    => MyChapterResource::collection($this->chapters),
        ];
    }
}
