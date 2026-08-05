<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required_without:phone|string',
            'phone' => 'required_without:email|string',
            'otp'   => 'required|string',
        ];
    }
}
