<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'academy_name' => $this->user_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_academy' => $this->phone_academy,
            'is_active' => $this->is_active,
            'domain' => $this->whenLoaded('tenant', function () {
                return $this->tenant?->domain;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
