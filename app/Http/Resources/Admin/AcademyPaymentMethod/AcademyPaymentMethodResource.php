<?php

namespace App\Http\Resources\Admin\AcademyPaymentMethod;

use Illuminate\Http\Resources\Json\JsonResource;

class AcademyPaymentMethodResource extends JsonResource
{
    // AcademyPaymentMethodResource.php
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'payment_method_id' => $this->payment_method_id,
            'gateway'           => $this->gateway,
            'credentials'       => is_string($this->credentials)
                ? json_decode($this->credentials, true)
                : $this->credentials,
            'is_active'         => $this->is_active,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
