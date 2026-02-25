<?php

namespace App\Http\Requests\Admin\Features;
use App\Http\Requests\BaseRequest\BaseRequest;
class FeaturesUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'key' => 'sometimes|required|string|max:255',
        ];
    }
}
