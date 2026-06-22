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
            ],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
