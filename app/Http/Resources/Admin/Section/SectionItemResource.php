<?php

namespace App\Http\Resources\Admin\Section;

use Illuminate\Http\Resources\Json\JsonResource;

class SectionItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'section_id' => $this->section_id,
            'order'      => $this->order,

            'props'      => is_string($this->props) ? json_decode($this->props, true) : $this->props,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
