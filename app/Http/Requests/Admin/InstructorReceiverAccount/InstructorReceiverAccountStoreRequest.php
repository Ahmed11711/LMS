<?php

namespace App\Http\Requests\Admin\InstructorReceiverAccount;

use App\Http\Requests\BaseRequest\BaseRequest;

class InstructorReceiverAccountStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_account_id' => 'required|integer|exists:receiver_accounts,id',
            'account_value' => 'required|string|max:255',
            'is_active' => 'required|integer',
        ];
    }
}
