<?php

namespace App\Http\Resources\User\Bag;

use Illuminate\Http\Resources\Json\JsonResource;

class BagResource extends JsonResource
{
    public function toArray($request): array
    {
        $isPurchased = $this->isPurchasedByCurrentUser();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image' => $this->image,

            'category_bag_id' => $this->category_bag_id,

            // Pricing
            'type_price' => $this->type_price,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'currency' => $this->currency,

            // Download policy
            'download_type' => $this->download_type,
            'download_limit' => $this->download_limit,
            'download_validity_days' => $this->download_validity_days,

            // Stats
            'count_view' => $this->count_view,
            'status' => $this->status,

            // 🔑 فلاج بيبين هل اليوزر اشترى الحقيبة دي فعلاً وموافق عليها
            'is_purchased' => $isPurchased,

            // 🔒 الـ items: لو مش مشترى، path يترجع null
            'items' => $this->whenLoaded('items', function () use ($isPurchased) {
                return $this->items->map(function ($item) use ($isPurchased) {
                    return [
                        'id' => $item->id,
                        'bag_id' => $item->bag_id,
                        'path' => $isPurchased ? $item->path : null,
                        'type' => $item->type,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                });
            }),

            // ✅ الجاليري بيرجع عادي زي ما هو من غير أي تعديل
            'gallery' => $this->whenLoaded('gallery'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function isPurchasedByCurrentUser(): bool
    {
        $userId = auth('api')->id();

        if (!$userId) {
            return false;
        }

        if ($this->relationLoaded('purchases')) {
            return $this->purchases
                ->where('user_id', $userId)
                ->where('status', 'approved')
                ->isNotEmpty();
        }

        return $this->purchases()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->exists();
    }
}
