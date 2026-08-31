<?php

namespace App\Http\Requests\SuperAdmin\Academy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $routeParams = $this->route()?->parameters() ?? [];
        $userId = !empty($routeParams) ? array_values($routeParams)[0] : null;

        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'user_name'     => ['sometimes', 'string', 'max:255'],
            'email'         => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'id'),
            ],
            'phone'         => ['sometimes', 'string', 'max:20'],
            'phone_academy' => ['sometimes', 'string', 'max:20'],
            'is_active'     => ['sometimes', 'boolean'],
        ];
    }
}
