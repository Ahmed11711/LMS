<?php

namespace App\Http\Resources\User\PaymentInfo;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentInfoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'credentials' => $this->credentials,
            'is_active' => $this->is_active,
        ];
    }
}
