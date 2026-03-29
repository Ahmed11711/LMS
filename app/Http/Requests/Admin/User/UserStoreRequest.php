<?php

namespace App\Http\Requests\Admin\User;

use App\Http\Requests\BaseRequest\BaseRequest;

class UserStoreRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenant.users,email',
            'email_verified_at' => 'nullable|date',
            'password' => 'required|string|min:8|max:255',
            'profile_image' => 'nullable|file|max:255',
            'role' => 'required|string|in:admin,user,instructor',
            'specialties' => 'nullable|string|max:255',
        ];
    }
}
