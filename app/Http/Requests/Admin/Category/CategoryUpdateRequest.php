<?php

namespace App\Http\Requests\Admin\Category;
use App\Http\Requests\BaseRequest\BaseRequest;
class CategoryUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|sometimes|string',
            'is_active' => 'sometimes|required|integer',
        ];
    }
}
