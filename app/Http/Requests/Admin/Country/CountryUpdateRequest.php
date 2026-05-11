<?php

namespace App\Http\Requests\Admin\Country;
use App\Http\Requests\BaseRequest\BaseRequest;
class CountryUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'sometimes|required|integer',
        ];
    }
}
