<?php

namespace App\Http\Requests\Admin\Bag;

use App\Http\Requests\BaseRequest\BaseRequest;

class BagStoreRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|image|max:2048',
            'category_name' => 'nullable|string|max:255',
            'type_price' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lte:price',
            'is_active' => 'sometimes|boolean',

            'items' => 'nullable|array',
            'items.*.file' => 'required_with:items|file|max:20480',
            'items.*.type' => 'required_with:items|string|max:255',

            'payment_info_ids' => 'nullable|array',
            'payment_info_ids.*' => 'integer|exists:user_payment_infos,id',
        ];
    }
}
