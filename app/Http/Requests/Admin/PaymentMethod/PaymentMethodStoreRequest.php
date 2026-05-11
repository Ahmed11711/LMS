<?php

namespace App\Http\Requests\Admin\PaymentMethod;

use App\Http\Requests\BaseRequest\BaseRequest;

class PaymentMethodStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => 'required|integer|exists:countries,id',
            'type' => 'required|string|in:online,direct',
            'display_name' => 'required|string|max:255',
            'credentials'  => 'required|array',
            'credentials.*' => 'required|string',
            'is_active' => 'required|integer',
        ];
    }
}
