<?php

namespace App\Http\Requests\Central\Auth;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateInfoAcademy extends BaseRequest
{

    public function rules(): array
    {
        return [
            'email' => 'nullable|email|required_without:phone|exists:users,email',
            'phone' => 'nullable|string|required_without:email|exists:users,phone',
            'phone_academy' => 'required|string|min:10',
            'username' => 'required|string|unique:users,username',
            'country_code' => 'required|string',
            'specialties' => 'required|string',
            'link_academy'  => 'required|string|unique:tenants,domain',
        ];
    }
}
