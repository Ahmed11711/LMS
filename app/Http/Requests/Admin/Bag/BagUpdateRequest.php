<?php

namespace App\Http\Requests\Admin\Bag;

use App\Http\Requests\BaseRequest\BaseRequest;

class BagUpdateRequest extends BaseRequest
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
            'title' => 'sometimes|required|string|max:255',
            'short_description' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'image' => 'sometimes|nullable|file|image|max:2048',
            'category_name' => 'sometimes|nullable|string|max:255',
            'type_price' => 'sometimes|nullable|string|max:255',
            'price' => 'sometimes|nullable|numeric|min:0',
            'discount_price' => 'sometimes|nullable|numeric|min:0|lte:price',
            'count_download' => 'sometimes|nullable|string|max:255',
            'count_view' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|required|boolean',

            'items' => 'sometimes|array',
            'items.*.file' => 'required_with:items|file|max:20480',
            'items.*.type' => 'required_with:items|string|max:255',

            'payment_info_ids' => 'sometimes|array',
            'payment_info_ids.*' => 'integer|exists:instructor_receiver_accounts,id',
            'gallery' => 'nullable|array',
            'gallery.*' => 'file|image|max:2048',
        ];
    }
}
