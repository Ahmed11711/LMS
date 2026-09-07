<?php

namespace App\Http\Requests\Admin\Bag;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Validation\Rule;

class BagStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|image|',

            'category_bag_id' => 'nullable|integer|exists:category_bags,id',

            // Pricing
            'type_price' => ['nullable', Rule::in(['free', 'paid'])],
            'price' => 'nullable|required_if:type_price,paid|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lte:price',
            'currency' => 'nullable|string|max:10',

            // Download policy
            'download_type' => ['nullable', Rule::in(['unlimited', 'limited'])],
            'download_limit' => 'nullable|required_if:download_type,limited|integer|min:1',
            'download_validity_days' => 'nullable|integer|min:1',

            // Visibility
            'status' => ['sometimes', Rule::in(['hidden', 'draft', 'published'])],

            'items' => 'nullable|array',
            'items.*.file' => 'required_with:items|file|',
            'items.*.type' => 'required_with:items|string|',

            'payment_info_ids' => 'nullable|array',
            'payment_info_ids.*' => 'integer|exists:instructor_receiver_accounts,id',
            'gallery' => 'nullable|array',
            'gallery.*' => 'file|image|',
        ];
    }
}
