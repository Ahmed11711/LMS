<?php

namespace App\Http\Requests\Admin\CategoryBag;
use App\Http\Requests\BaseRequest\BaseRequest;
class CategoryBagStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
