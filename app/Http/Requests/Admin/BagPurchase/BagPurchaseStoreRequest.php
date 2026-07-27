<?php

namespace App\Http\Requests\Admin\BagPurchase;
use App\Http\Requests\BaseRequest\BaseRequest;
class BagPurchaseStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bag_id' => 'required|integer|exists:bags,id',
            'user_id' => 'required|integer|exists:users,id',
            'payment_info_id' => 'nullable|integer|exists:payment_infos,id',
            'receipt' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
            'status' => 'required|string|max:255',
            'rejection_reason' => 'nullable|string',
        ];
    }
}
