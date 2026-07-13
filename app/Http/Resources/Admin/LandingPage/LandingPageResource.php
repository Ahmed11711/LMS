<?php

namespace App\Http\Resources\Admin\LandingPage;

use Illuminate\Http\Resources\Json\JsonResource;

class LandingPageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'template_name' => $this->template_name,
            'content' => $this->content,
            'is_active' => $this->is_active,
            'course_id' => $this->course_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
