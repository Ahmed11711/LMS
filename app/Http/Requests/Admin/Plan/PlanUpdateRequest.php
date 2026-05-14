<?php

namespace App\Http\Requests\Admin\Plan;
use App\Http\Requests\BaseRequest\BaseRequest;
class PlanUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_type' => 'sometimes|required|string|max:255',
            'owner_id' => 'sometimes|required|integer',
            'name' => 'sometimes|required|string|max:255',
            'desc' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'duration_value' => 'sometimes|required|integer',
            'duration_unit' => 'sometimes|required|in:days,months,years',
            'status' => 'sometimes|required|in:active,draft',
        ];
    }
}
