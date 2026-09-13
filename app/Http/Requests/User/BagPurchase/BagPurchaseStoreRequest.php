<?php

namespace App\Http\Requests\User\BagPurchase;

use App\Http\Requests\BaseRequest\BaseRequest;
use App\Models\Bag;

class BagPurchaseStoreRequest extends BaseRequest
{
    public function rules(): array
    {
        $bag = Bag::find($this->input('bag_id'));
        $isFree = $bag && $bag->type_price === 'free';

        return [
            'bag_id' => 'required|integer|exists:bags,id',
            'payment_info_id' => [
                $isFree ? 'nullable' : 'required',
                'integer',
                'exists:instructor_receiver_accounts,id',
            ],
            'receipt' => [
                $isFree ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required' => 'لازم ترفع صورة أو ملف الإيصال.',
            'bag_id.exists' => 'المنتج غير موجود.',
            'payment_info_id.required' => 'لازم تختار وسيلة الدفع.',
        ];
    }
}
