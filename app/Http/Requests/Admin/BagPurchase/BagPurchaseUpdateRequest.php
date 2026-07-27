<?php

namespace App\Http\Requests\Admin\BagPurchase;
use App\Http\Requests\BaseRequest\BaseRequest;
class BagPurchaseUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bag_id' => 'sometimes|required|integer|exists:bags,id',
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'payment_info_id' => 'nullable|sometimes|integer|exists:payment_infos,id',
            'receipt' => 'sometimes|required|string|max:255',
            'amount' => 'nullable|sometimes|numeric',
            'status' => 'sometimes|required|string|max:255',
            'rejection_reason' => 'nullable|sometimes|string',
        ];
    }
}
