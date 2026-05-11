<?php

namespace App\Http\Requests\Admin\paymentInfo;
use App\Http\Requests\BaseRequest\BaseRequest;
class paymentInfoStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'is_active' => 'required|integer',
        ];
    }
}
