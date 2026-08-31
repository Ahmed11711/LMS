<?php

namespace App\Http\Requests\SuperAdmin\Academy;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AcademyUpdateRequest extends BaseRequest
{

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'user_name'     => ['sometimes', 'string', 'max:255'],
            'email'         => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $userId],
            'phone'         => ['sometimes', 'string', 'max:20'],
            'phone_academy' => ['sometimes', 'string', 'max:20'],
            'is_active'     => ['sometimes', 'boolean'],
        ];
    }
}
