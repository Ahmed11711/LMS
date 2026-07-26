<?php

namespace App\Http\Resources\Admin\Bag;

use App\Http\Resources\Admin\Bag\BagItemResource;
use App\Http\Resources\Admin\BagGallery\BagGalleryResource;
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
            'category_name' => $this->category_name,
            'type_price' => $this->type_price,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'count_download' => $this->count_download,
            'count_view' => $this->count_view,
            'is_active' => $this->is_active,
            'items' => BagItemResource::collection($this->whenLoaded('items')),
            'gallery' => BagGalleryResource::collection($this->whenLoaded('gallery')),
            'payment_infos' => UserPaymentInfoResource::collection($this->whenLoaded('userPaymentInfos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
