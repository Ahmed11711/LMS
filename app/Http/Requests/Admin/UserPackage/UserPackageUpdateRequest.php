<?php

namespace App\Http\Requests\Admin\UserPackage;
use App\Http\Requests\BaseRequest\BaseRequest;
class UserPackageUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'package_id' => 'sometimes|required|integer|exists:packages,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
            'active' => 'sometimes|required|integer',
            'transaction_id' => 'nullable|sometimes|string|max:255',
            'status' => 'sometimes|required|in:pending,active,expired,cancelled',
            'price' => 'sometimes|required|numeric',
        ];
    }
}
