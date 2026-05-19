<?php

namespace App\Http\Resources\Admin\ReceiverAccount;

use Illuminate\Http\Resources\Json\JsonResource;

class ReceiverAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
            'logo' => $this->logo
                ? asset(ltrim($this->logo, '/'))
                : null,
            'country_code' => $this->country_code,
            'country_name' => $this->country_name,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
