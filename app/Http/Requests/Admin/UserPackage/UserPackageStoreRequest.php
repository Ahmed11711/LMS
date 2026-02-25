<?php

namespace App\Http\Requests\Admin\UserPackage;
use App\Http\Requests\BaseRequest\BaseRequest;
class UserPackageStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'package_id' => 'required|integer|exists:packages,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'active' => 'required|integer',
            'transaction_id' => 'nullable|string|max:255',
            'status' => 'required|in:pending,active,expired,cancelled',
            'price' => 'required|numeric',
        ];
    }
}
