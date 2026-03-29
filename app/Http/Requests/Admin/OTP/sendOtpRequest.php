<?php

namespace App\Http\Requests\Admin\OTP;

use App\Http\Requests\BaseRequest\BaseRequest;

class sendOtpRequest extends BaseRequest
{


    public function rules(): array
    {
        return [
            'contact' => 'required',
        ];
    }
}
