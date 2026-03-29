<?php

namespace App\Http\Requests\Central;

use App\Http\Requests\BaseRequest\BaseRequest;

class CreatePaymentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'package_id' => 'required|exists:packages,id',

            'email' => [
                'nullable',
                'email',
                'required_without:phone',
                'exists:users,email'
            ],

            'phone' => [
                'nullable',
                'string',
                'required_without:email',
                'exists:users,phone'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Email or phone is required.',
            'phone.required_without' => 'Phone or email is required.',
            'email.exists' => 'This email is not registered.',
            'phone.exists' => 'This phone is not registered.',
        ];
    }
}
