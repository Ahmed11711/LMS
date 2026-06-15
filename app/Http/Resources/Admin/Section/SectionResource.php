<?php

namespace App\Http\Resources\Admin\Section;

use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'pages_id' => $this->pages_id,
            'type' => $this->type,
            'order' => $this->order,
            'props' => $this->props,
            'items'      => SectionItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
