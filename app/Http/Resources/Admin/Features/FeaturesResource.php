<?php

namespace App\Http\Resources\Admin\Features;

use Illuminate\Http\Resources\Json\JsonResource;

class FeaturesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'key' => $this->key,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
