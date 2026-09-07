<?php

namespace App\Http\Requests\Admin\Bag;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Validation\Rule;

class BagUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'short_description' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'image' => 'sometimes|nullable|file|image|max:2048',

            'category_bag_id' => 'sometimes|nullable|integer|exists:category_bags,id',

            // Pricing
            'type_price' => ['sometimes', 'nullable', Rule::in(['free', 'paid'])],
            'price' => 'sometimes|nullable|required_if:type_price,paid|numeric|min:0',
            'discount_price' => 'sometimes|nullable|numeric|min:0|lte:price',
            'currency' => 'sometimes|nullable|string|max:10',

            // Download policy
            'download_type' => ['sometimes', 'nullable', Rule::in(['unlimited', 'limited'])],
            'download_limit' => 'sometimes|nullable|required_if:download_type,limited|integer|min:1',
            'download_validity_days' => 'sometimes|nullable|integer|min:1',

            // Visibility
            'status' => ['sometimes', Rule::in(['hidden', 'draft', 'published'])],

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
