<?php

namespace App\Http\Requests\Admin\UserPaymentInfo;

use App\Http\Requests\BaseRequest\BaseRequest;

class UserPaymentInfoStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_info_id' => 'required|integer|exists:payment_infos,id',
            'value' => 'required|array',
            'account_name' => 'nullable|string|max:255',
            'is_default' => 'required|integer',
        ];
    }
}
