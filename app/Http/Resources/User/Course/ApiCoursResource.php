<?php

namespace App\Http\Resources\User\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiCoursResource extends JsonResource
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
            'is_enrolled' => $this->when(
                auth('api')->check(),
                fn() => (bool) $this->is_enrolled
            ),
            'description' => $this->description,
            'image' => $this->image
                ? asset(ltrim($this->image, '/'))
                : null,
            'price' => $this->price,
            'final_price' => $this->final_price,
            'type' => $this->type,
            'slug' => $this->slug,
            'currency' => $this->currency,

        ];
    }
}
