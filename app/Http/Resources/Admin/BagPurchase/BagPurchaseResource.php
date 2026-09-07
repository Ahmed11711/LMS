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
            'bag' => [
                'id' => $this->whenLoaded('bag', fn() => $this->bag->id),
                'title' => $this->whenLoaded('bag', fn() => $this->bag->title),
                'price' => $this->whenLoaded('bag', fn() => $this->bag->price),
            ],
            'user_id' => $this->user_id,
            'user' => [
                'id' => $this->whenLoaded('user', fn() => $this->user->id),
                'name' => $this->whenLoaded('user', fn() => $this->user->name),
                'email' => $this->whenLoaded('user', fn() => $this->user->email),
            ],
            'payment_info' => $this->whenLoaded('paymentInfo', fn() => [

                'title' => $this->paymentInfo->title,
            ]),
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
