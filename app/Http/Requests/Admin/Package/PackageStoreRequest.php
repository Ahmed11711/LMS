<?php

namespace App\Http\Requests\Admin\Package;

use App\Http\Requests\BaseRequest\BaseRequest;

class PackageStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titile' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'is_active' => 'nullable|integer',
            'duration_months' => 'required|integer',
            'order' => 'nullable|integer',
            'recomnd' => 'nullable|integer',
        ];
    }
}
