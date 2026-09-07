<?php

namespace App\Http\Requests\Admin\CategoryBag;
use App\Http\Requests\BaseRequest\BaseRequest;
class CategoryBagUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
        ];
    }
}
