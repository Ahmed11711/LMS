<?php

namespace App\Http\Resources\Admin\AcademyWithdraw;

use Illuminate\Http\Resources\Json\JsonResource;

class AcademyWithdrawResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'academy_id' => $this->academy_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'admin_note' => $this->admin_note,
            'approved_by' => $this->approved_by,
            'payment_method' => $this->payment_method,
            'payment_details' => $this->payment_details,
            'receipt_image' => $this->receipt_image,
            'transaction_number' => $this->transaction_number,
            'transaction_id' => $this->transaction_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
