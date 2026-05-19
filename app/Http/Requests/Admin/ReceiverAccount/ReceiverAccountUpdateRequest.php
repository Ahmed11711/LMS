<?php

namespace App\Http\Requests\Admin\ReceiverAccount;

use App\Http\Requests\BaseRequest\BaseRequest;

class ReceiverAccountUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'key' => 'sometimes|required|string|max:255|unique:receiver_accounts,key,' . $this->route('receiverAccount') . ',id',
            'logo' => 'sometimes|required|image|mimes:jpeg,png,jpg,svg,webp',
            'country_code' => 'nullable|sometimes|string|max:255',
            'country_name' => 'nullable|sometimes|string|max:255',
            'is_active' => 'sometimes|required|integer',
        ];
    }
}
