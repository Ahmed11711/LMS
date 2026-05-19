<?php

namespace App\Http\Requests\Admin\AcademyWithdraw;

use App\Http\Requests\BaseRequest\BaseRequest;

class AcademyWithdrawStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'admin_note' => 'nullable|string',
            'approved_by' => 'nullable|integer',
            'payment_method' => 'required|string|max:255',
            'payment_details' => 'nullable|array',
        ];
    }
}
