<?php

namespace App\Http\Requests\Admin\Withdraw;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'amount'                => 'sometimes|numeric|min:1',
            'status'                => 'sometimes|in:pending,rejected,approved',
            'admin_note'            => 'nullable|string',
            'receipt_image'         => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'transaction_number'    => 'required|string|unique:user_withdraws,transaction_number',
        ];
    }
}
