<?php

namespace App\Http\Resources\Admin\Bag;

use Illuminate\Http\Resources\Json\JsonResource;

class BagItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'bag_id' => $this->bag_id,
            'path' => $this->path,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
