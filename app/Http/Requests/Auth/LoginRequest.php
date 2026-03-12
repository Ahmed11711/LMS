<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'email'    => 'required_without:phone|string',
            'phone'    => 'required_without:email|string',
            'password' => 'required|string'
        ];
    }
}
