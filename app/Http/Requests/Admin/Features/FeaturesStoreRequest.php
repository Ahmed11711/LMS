<?php

namespace App\Http\Requests\Admin\Features;

use App\Http\Requests\BaseRequest\BaseRequest;

class FeaturesStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
        ];
    }
}
