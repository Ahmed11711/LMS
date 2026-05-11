<?php

namespace App\Http\Resources\Admin\paymentInfo;

use Illuminate\Http\Resources\Json\JsonResource;

class paymentInfoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
