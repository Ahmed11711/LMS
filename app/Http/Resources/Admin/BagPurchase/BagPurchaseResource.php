<?php

namespace App\Http\Resources\Admin\BagPurchase;

use Illuminate\Http\Resources\Json\JsonResource;

class BagPurchaseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'bag_id' => $this->bag_id,
            'user_id' => $this->user_id,
            'payment_info_id' => $this->payment_info_id,
            'receipt' => $this->receipt,
            'amount' => $this->amount,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
