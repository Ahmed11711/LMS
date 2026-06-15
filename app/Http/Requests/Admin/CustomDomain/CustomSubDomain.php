<?php

namespace App\Http\Requests\Admin\CustomDomain;

use App\Http\Requests\BaseRequest\BaseRequest;

class CustomSubDomain extends BaseRequest
{
    public function rules(): array
    {
        return [
            'domain' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'domain.regex' => 'يجب أن يكون الـ subdomain أحرف وأرقام فقط مثل: my-academy',
        ];
    }
}
