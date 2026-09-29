<?php

namespace App\Http\Resources\User\Bag;

use App\Http\Resources\Admin\InstructorReceiverAccount\InstructorReceiverAccountResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BagResource extends JsonResource
{
    protected const APPROVED_STATUSES = ['approved', 'accepted'];
    protected const PENDING_STATUSES  = ['pending'];

    public function toArray($request): array
    {
        $purchaseStatus = $this->currentUserPurchaseStatus();
        $canAccessFiles = $purchaseStatus === 'purchased';

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

            // none | pending | purchased
            'is_purchased' => $purchaseStatus,

            'items' => $this->whenLoaded('items', function () use ($canAccessFiles) {
                return $this->items->map(function ($item) use ($canAccessFiles) {
                    return [
                        'id' => $item->id,
                        'bag_id' => $item->bag_id,
                        'path' => $canAccessFiles ? $item->path : null,
                        'type' => $item->type,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                });
            }),

            'gallery' => $this->whenLoaded('gallery'),

            'payment_infos' => InstructorReceiverAccountResource::collection(
                $this->whenLoaded('userPaymentInfos')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Returns: 'none' | 'pending' | 'purchased'
     */
    protected function currentUserPurchaseStatus(): string
    {
        $userId = auth('api')->id();

        if (!$userId) {
            return 'none';
        }

        $statuses = $this->relationLoaded('purchases')
            ? $this->purchases->where('user_id', $userId)->pluck('status')
            : $this->purchases()->where('user_id', $userId)->pluck('status');

        // الأولوية للـ approved حتى لو في pending قديم
        if ($statuses->intersect(self::APPROVED_STATUSES)->isNotEmpty()) {
            return 'purchased';
        }

        if ($statuses->intersect(self::PENDING_STATUSES)->isNotEmpty()) {
            return 'pending';
        }

        return 'none';
    }
}
