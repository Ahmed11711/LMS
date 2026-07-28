<?php

namespace App\Http\Resources\Admin\Course;

use App\Http\Resources\Admin\Category\CategoryResource;

use App\Http\Resources\Admin\Chapter\ChapterResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\User\UserResource;

class CourseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'image' => $this->image
                ? asset(ltrim($this->image, '/'))
                : null,
            'price_type' => $this->price_type,
            'price' => $this->price,
            'final_price' => $this->final_price,
            'status' => $this->status,
            'slug' => $this->slug,
            'currency' => $this->currency,
            'chapters'    => ChapterResource::collection($this->whenLoaded('chapters')),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'user'        => new UserResource($this->whenLoaded('user')),
            'infos'    =>  $this->whenLoaded('infos'),
            'receiver_accounts' => $this->whenLoaded('courseReceiverAccounts', function () {
                return $this->courseReceiverAccounts->map(fn($item) => [
                    'id'            => $item->id,
                    'account_value' => $item->instructorReceiverAccount->account_value,
                    'name'          => $item->instructorReceiverAccount->receiverAccount->name,
                    'logo'          => asset($item->instructorReceiverAccount->receiverAccount->logo),
                    'country_code'  => $item->instructorReceiverAccount->receiverAccount->country_code,
                    'country_name'  => $item->instructorReceiverAccount->receiverAccount->country_name,
                ]);
            }),
            'subscribers_count' => $this->when(isset($this->active_subscribers_count), $this->active_subscribers_count),
            'total_sales' => $this->when(isset($this->total_sales), (float) $this->total_sales),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
