<?php

namespace App\Http\Requests\Admin\CustomDomain;

use App\Http\Requests\BaseRequest\BaseRequest;

class CustomDomainRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'domain' => 'required|string'

        ];
    }
}
