<?php

namespace App\Http\Resources\User\Bag;

use App\Http\Resources\Admin\Bag\BagItemResource;
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

            'items' => BagItemResource::collection($this->whenLoaded('items')),

            // 🔒 الجاليري: لو مش مشترى، اللينكات كلها null
            'gallery' => $this->whenLoaded('gallery', function () use ($isPurchased) {
                return $this->gallery->map(function ($item) use ($isPurchased) {
                    return [
                        'id' => $item->id,
                        'image' => $isPurchased ? $item->image : null,
                        'created_at' => $item->created_at,
                    ];
                });
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * هل اليوزر الحالي اشترى الحقيبة دي وتم الموافقة عليها؟
     */
    protected function isPurchasedByCurrentUser(): bool
    {
        $userId = auth('api')->id();

        if (!$userId) {
            return false; // زائر مش لوجين
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
