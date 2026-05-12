<?php

namespace App\Http\Resources\User\Lesson;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonCommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'body'       => $this->body,
            'created_at' => $this->created_at,
            'user'       => [
                'id'            => $this->user->id,
                'name'          => $this->user->name,
                'profile_image' => $this->user->profile_image,
            ],
            'likes_count' => $this->likes->count(),
            'replies'     => LessonCommentResource::collection($this->replies),
        ];
    }
}
