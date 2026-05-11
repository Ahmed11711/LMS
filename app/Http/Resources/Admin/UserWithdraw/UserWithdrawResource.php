<?php

namespace App\Http\Resources\Admin\UserWithdraw;

use Illuminate\Http\Resources\Json\JsonResource;

class UserWithdrawResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_payment_info_id' => $this->user_payment_info_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'admin_note' => $this->admin_note,
            'admin_id' => $this->admin_id,
            'transaction_id' => $this->transaction_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
