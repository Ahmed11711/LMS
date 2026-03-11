<?php

namespace App\Http\Resources\Admin\Me;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class MeResource extends JsonResource
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
            'statusPayed' => Arr::random(['paid', 'expired', 'free_trial', 'pending']),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,


        ];
    }
}
