<?php

namespace App\Http\Requests\User\Profile;

use App\Http\Requests\BaseRequest\BaseRequest;


class ChangePasswordRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required.',
            'password.required'         => 'New password is required.',
            'password.min'              => 'New password must be at least 8 characters.',
            'password.confirmed'        => 'Password confirmation does not match.',
        ];
    }
}
