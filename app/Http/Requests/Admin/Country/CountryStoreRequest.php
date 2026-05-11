<?php

namespace App\Http\Requests\Admin\Country;
use App\Http\Requests\BaseRequest\BaseRequest;
class CountryStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'is_active' => 'required|integer',
        ];
    }
}
