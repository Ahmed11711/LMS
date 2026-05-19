<?php

namespace App\Http\Requests\Admin\AcademyWithdraw;

use App\Http\Requests\BaseRequest\BaseRequest;

class AcademyWithdrawUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'sometimes|required|string|max:255',
            'payment_details' => 'nullable|sometimes|array',
        ];
    }
}
