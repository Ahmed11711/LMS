<?php

namespace App\Http\Resources\Admin\Bag;

use App\Http\Resources\Admin\Bag\BagItemResource;
use App\Http\Resources\Admin\BagGallery\BagGalleryResource;
use App\Http\Resources\Admin\CategoryBag\CategoryBagResource;
use App\Http\Resources\Admin\UserPaymentInfo\UserPaymentInfoResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BagResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image' => $this->image,

            'category_bag_id' => $this->category_bag_id,
            // 'category' => new CategoryBagResource($this->whenLoaded('category')),

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

            'items' => BagItemResource::collection($this->whenLoaded('items')),
            'gallery' => BagGalleryResource::collection($this->whenLoaded('gallery')),
            'payment_infos' => UserPaymentInfoResource::collection($this->whenLoaded('userPaymentInfos')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
