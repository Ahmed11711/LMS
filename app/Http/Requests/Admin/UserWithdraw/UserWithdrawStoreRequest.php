<?php

namespace App\Http\Requests\Admin\UserWithdraw;

use App\Http\Requests\BaseRequest\BaseRequest;

class UserWithdrawStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_payment_info_id' => 'required|integer|exists:user_payment_infos,id',
            'amount' => 'required|numeric',

        ];
    }
}
