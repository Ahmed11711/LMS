<?php

namespace App\Http\Resources\Admin\InstructorReceiverAccount;

use Illuminate\Http\Resources\Json\JsonResource;

class InstructorReceiverAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'receiver_account_id' => $this->receiver_account_id,
            'account_value' => $this->account_value,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
