<?php

namespace App\Http\Requests\Tenant;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:tenants,name',
            'domain' => 'required|string|unique:tenants,domain',
        ];
    }
}
