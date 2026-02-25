<?php

namespace App\Http\Requests\Central\Auth;

use App\Http\Requests\BaseRequest\BaseRequest;

class CreateAcademyRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => 'nullable|email|required_without:phone|unique:users,email',
            'phone' => 'nullable|string|required_without:email|unique:users,phone',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Email or phone is required.',
            'phone.required_without' => 'Phone or email is required.',
            'email.email' => 'Email must be a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ];
    }
}
