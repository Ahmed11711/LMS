<?php

namespace App\Http\Resources\Admin\UserSubscribe;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscribeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at ?? null,
            'receipt' => $this->receipt
                ? asset('storage/' . ltrim(
                    preg_replace('#^https?://[^/]+/(storage/)?#', '', $this->receipt),
                    '/'
                ))
                : null,
            'status' => $this->status,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course->id,
                    'title' => $this->course->title,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
        ];
    }
}
