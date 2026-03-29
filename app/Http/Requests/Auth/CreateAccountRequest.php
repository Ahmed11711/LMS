<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Validation\Rule;

class CreateAccountRequest extends BaseRequest
{


    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenant.users,email',
            'password' => 'required|string|min:6'
        ];
    }
}
