<?php

namespace App\Http\Resources\User\BagPurchase;

use App\Http\Resources\User\PaymentInfo\PaymentInfoResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BagPurchaseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'bag' => [
                'id' => $this->bag->id,
                'title' => $this->bag->title,
                'image' => $this->bag->image
                    ? asset(ltrim($this->bag->image, '/'))
                    : null,
            ],
            'amount' => $this->amount,
            'receipt' => $this->receipt,
            'status' => $this->status,
            'payment_info' => PaymentInfoResource::make($this->whenLoaded('paymentInfo')),
            'rejection_reason' => $this->when($this->status === 'rejected', $this->rejection_reason),
            'created_at' => $this->created_at,
        ];
    }
}
