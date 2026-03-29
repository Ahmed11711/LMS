<?php

namespace App\Http\Resources\BaseResource\MetaResource;

use Illuminate\Http\Resources\Json\JsonResource;

class MetaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'current_page' => $this->currentPage(),
            'last_page'    => $this->lastPage(),
            'per_page'     => $this->perPage(),
            'total'        => $this->total(),
        ];
    }
}
