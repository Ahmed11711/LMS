<?php

namespace App\Http\Requests\Admin\UserPackage;

use Illuminate\Foundation\Http\FormRequest;

class UpgradeRequestStoreRequest extends FormRequest
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
