<?php

namespace App\Http\Resources\Admin\BagGallery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BagGalleryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'image' => $this->image,
            'created_at' => $this->created_at,
        ];
    }
}
