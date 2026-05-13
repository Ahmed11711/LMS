<?php

namespace App\Http\Requests\Admin\paymentInfo;

use App\Http\Requests\BaseRequest\BaseRequest;

class paymentInfoUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|required|integer',
            'credentials'  => 'sometimes|array',
            'credentials.*' => 'sometimes|string',
        ];
    }
}
