<?php

namespace App\Http\Resources\Admin\Plan;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'desc' => $this->desc,
            'price' => $this->price,
            'duration_value' => $this->duration_value,
            'duration_unit' => $this->duration_unit,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
