<?php

namespace App\Http\Requests\User\Profile;

use App\Http\Requests\BaseRequest\BaseRequest;

class UpdateProfileRequest extends BaseRequest
{
    public function rules(): array
    {
        $userId = auth('api')->id();

        return [
            'name'          => 'sometimes|string|max:255',
            'email'         => "sometimes|email|unique:users,email,{$userId}",
            'phone'         => "sometimes|string|unique:users,phone,{$userId}",
            'username'      => "sometimes|string|unique:users,username,{$userId}",
            'country_code'  => 'sometimes|string|max:10',
            'specialties'   => 'sometimes|string|max:255',
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
