<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'password' => $this->password,
            'remember_token' => $this->remember_token,
            'profile_image' => $this->profile_image,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'specialties' => $this->specialties,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
