<?php

namespace App\Http\Requests\Admin\User;

use App\Http\Requests\BaseRequest\BaseRequest;

class UserUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|max:255|unique:tenant.users,email,' . $this->route('user') . ',id',
            'email_verified_at' => 'nullable|sometimes|date',
            'password' => 'sometimes|required|string|max:255',
            'profile_image' => 'nullable|file|max:255',
            'role' => 'nullable|string|in:admin,user,instructor',
            'specialties' => 'nullable|string|max:255',
        ];
    }
}
