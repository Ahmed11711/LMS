<?php

namespace App\Http\Requests\User\BagPurchase;

use App\Http\Requests\BaseRequest\BaseRequest;

class BagPurchaseStoreRequest extends BaseRequest
{


    public function rules(): array
    {
        return [
            'bag_id' => 'required|integer|exists:bags,id',
            'payment_info_id' => [
                'required',
                'integer',
                'exists:instructor_receiver_accounts,id',
            ],
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required' => 'لازم ترفع صورة أو ملف الإيصال.',
            'bag_id.exists' => 'المنتج غير موجود.',
        ];
    }
}
