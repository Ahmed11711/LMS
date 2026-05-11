<?php

namespace App\Http\Requests\Admin\UserPaymentInfo;

use App\Http\Requests\BaseRequest\BaseRequest;

class UserPaymentInfoUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_info_id' => 'sometimes|required|integer|exists:payment_infos,id',
            'value' => 'sometimes|required|array',
            'account_name' => 'nullable|sometimes|string|max:255',
            'is_default' => 'sometimes|required|integer',
        ];
    }
}
