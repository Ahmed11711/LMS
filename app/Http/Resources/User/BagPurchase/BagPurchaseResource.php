<?php

namespace App\Http\Resources\User\BagPurchase;

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
            'rejection_reason' => $this->when($this->status === 'rejected', $this->rejection_reason),
            'created_at' => $this->created_at,
        ];
    }
}
