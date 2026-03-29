<?php

namespace App\Http\Resources\Admin\PhysicalCourseDetail;

use Illuminate\Http\Resources\Json\JsonResource;

class PhysicalCourseDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'address' => $this->address,
            'map_url' => $this->map_url,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'attachment' => $this->attachment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
