<?php

namespace App\Http\Requests\Admin\UserPackage;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpgradeRequestStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id'     => ['required', 'exists:LMS_CENTER.packages,id'],
            'payment_proof'  => ['required', 'image', 'mimes:jpg,jpeg,png,pdf', 'max:4096'], // 4MB
        ];
    }
}
