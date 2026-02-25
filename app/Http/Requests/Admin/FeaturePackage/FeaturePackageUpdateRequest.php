<?php

namespace App\Http\Requests\Admin\FeaturePackage;
use App\Http\Requests\BaseRequest\BaseRequest;
class FeaturePackageUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => 'sometimes|required|integer|exists:packages,id',
            'feature_id' => 'sometimes|required|integer|exists:features,id',
            'value' => 'nullable|sometimes|string|max:255',
            'lable' => 'nullable|sometimes|string|max:255',
            'key_feature' => 'nullable|sometimes|string|max:255',
        ];
    }
}
