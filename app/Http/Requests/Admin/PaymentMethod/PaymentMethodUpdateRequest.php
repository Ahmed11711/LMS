<?php

namespace App\Http\Requests\Admin\PaymentMethod;

use App\Http\Requests\BaseRequest\BaseRequest;

class PaymentMethodUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => 'sometimes|required|integer|exists:countries,id',
            'type' => 'sometimes|required|string|max:255',
            'display_name' => 'sometimes|required|string|max:255',
            'credentials'  => 'sometimes|array',
            'credentials.*' => 'sometimes|string',
            'is_active' => 'sometimes|required|integer',
        ];
    }
}
