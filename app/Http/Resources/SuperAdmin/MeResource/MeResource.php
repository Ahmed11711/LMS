<?php

namespace App\Http\Resources\SuperAdmin\MeResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'role' => $this->role,
            'is_active' => $this->is_active,
            // 'profile_image' => $this->profile_image,
            'profile_image' => $this->profileImageUrl(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    private function profileImageUrl(): ?string
    {
        $image = $this->profile_image;

        if (!$image) return null;

        if (str_starts_with($image, 'http')) return $image;

        $path = ltrim($image, '/');

        if (!str_starts_with($path, 'storage/')) {
            $path = 'storage/' . $path;
        }

        return asset($path);
    }
}
