<?php

namespace App\Http\Resources\Admin\FeaturePackage;

use Illuminate\Http\Resources\Json\JsonResource;

class FeaturePackageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'package_id' => $this->package_id,
            'feature_id' => $this->feature_id,
            'value' => $this->value,
            'lable' => $this->lable,
            'key_feature' => $this->key_feature,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
