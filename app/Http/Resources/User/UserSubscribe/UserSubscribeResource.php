<?php

namespace App\Http\Resources\User\UserSubscribe;

use App\Http\Resources\Admin\Course\CourseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscribeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'course_id'      => $this->course_id,
            'starts_at'      => $this->starts_at,
            'transaction_id' => $this->transaction_id,
            'receipt'        => $this->receipt,
            'status'         => $this->status,
            'created_by'     => $this->created_by,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'course'         => new CourseResource($this->whenLoaded('course')),
        ];
    }
}
