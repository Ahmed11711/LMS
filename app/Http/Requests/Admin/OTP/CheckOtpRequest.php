<?php

namespace App\Http\Requests\Admin\OTP;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CheckOtpRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'contact' => 'required',
            'otp' => 'required',
        ];
    }
}
