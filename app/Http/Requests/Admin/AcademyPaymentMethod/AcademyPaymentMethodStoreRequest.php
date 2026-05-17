<?php

namespace App\Http\Requests\Admin\AcademyPaymentMethod;

use App\Http\Requests\BaseRequest\BaseRequest;

class AcademyPaymentMethodStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => 'required|integer',
            'gateway' => 'required|string|max:255',
            'credentials' => 'required|array',
            'is_active' => 'required|integer',
        ];
    }
}
