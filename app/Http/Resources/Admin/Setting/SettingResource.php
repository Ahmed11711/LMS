<?php

namespace App\Http\Resources\Admin\Setting;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SettingResource extends JsonResource
{
    // نفس الـ keys اللي متعرفة إنها صور في الـ Controller
    protected array $imageKeys = ['logo', 'avatar_url', 'logo_url'];

    public function toArray($request): array
    {
        $isImage = in_array($this->key, $this->imageKeys);

        return [
            'id'         => $this->id,
            'key'        => $this->key,
            'value'      => $isImage && $this->value
                ? $this->getFullUrl($this->value)
                : $this->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * لو الـ value بالفعل رابط كامل (http/https) رجّعه زي ما هو،
     * لو مسار نسبي (path) ابني منه رابط كامل.
     */
    protected function getFullUrl(string $value): string
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }
}
