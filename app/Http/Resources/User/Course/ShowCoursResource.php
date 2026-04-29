<?php

namespace App\Http\Resources\User\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Chapter\ChapterResource;


class ShowCoursResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'image' => $this->image,
            'price_type' => $this->price_type,
            'price' => $this->price,
            'final_price' => $this->final_price,
            'status' => $this->status,
            'slug'=>$this->slug,
             'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'instructor'=>$this->whenLoaded('user'),
            'infos'=>$this->whenLoaded('infos'),
            'chapters'    => ChapterResource::collection($this->whenLoaded('chapters')),
        ];
    }
}
