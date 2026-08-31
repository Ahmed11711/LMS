<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $attributes = $this->resource->getAttributes();

        $data = [
            'id' => $this->id,
            'name' => $this->name
        ];

        $fieldsToInclude = [
            'name',
            'email',
            'phone',
            'email_verified_at',
            // 'password',
            // 'remember_token',
            'profile_image',
            'role',
            'is_active',
            'specialties',
            'created_at',
            'updated_at'
        ];

        foreach ($fieldsToInclude as $field) {
            if (array_key_exists($field, $attributes)) {
                $data[$field] = $field === 'profile_image' && $this->{$field}
                    ? asset($this->{$field})
                    : $this->{$field};
            }
        }

        return $data;
    }
}
