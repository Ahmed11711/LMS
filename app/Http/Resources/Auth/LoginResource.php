<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'username' => $this->username,
            'role' => $this->role,
            'email_verified_at' => boolval($this->email_verified_at),
            'is_active' => boolval($this->is_active),
            'statusPayed' => $this->status_payment ?? 'free_trial',
            'created_at' => $this->created_at,

        ];
    }
}
