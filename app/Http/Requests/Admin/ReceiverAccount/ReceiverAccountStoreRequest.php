<?php

namespace App\Http\Requests\Admin\ReceiverAccount;

use App\Http\Requests\BaseRequest\BaseRequest;

class ReceiverAccountStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:receiver_accounts,key',
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg,webp',
            'country_code' => 'nullable|string|max:255',
            'country_name' => 'nullable|string|max:255',
            'is_active' => 'required|integer',
        ];
    }
}
