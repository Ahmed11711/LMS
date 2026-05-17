<?php

namespace App\Http\Requests\Admin\AcademyPaymentMethod;

use App\Http\Requests\BaseRequest\BaseRequest;

class AcademyPaymentMethodUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => 'sometimes|required|integer|',
            'gateway' => 'sometimes|required|string|max:255',
            'credentials' => 'sometimes|required|array',
            'is_active' => 'sometimes|required|integer',
        ];
    }
}
