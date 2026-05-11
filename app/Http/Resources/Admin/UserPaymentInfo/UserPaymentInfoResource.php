<?php

namespace App\Http\Resources\Admin\UserPaymentInfo;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPaymentInfoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'payment_info_id' => $this->payment_info_id,
            'value' => $this->value,
            'account_name' => $this->account_name,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
