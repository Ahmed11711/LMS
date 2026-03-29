<?php

namespace App\Http\Requests\Admin\Course;
use App\Http\Requests\BaseRequest\BaseRequest;
class CourseUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'type' => 'sometimes|required|in:recorded,online,physical',
            'category_id' => 'nullable|sometimes|integer|exists:categories,id',
            'description' => 'nullable|sometimes|string',
            'image' => 'nullable|sometimes|string|max:255|file|max:2048',
            'price_type' => 'sometimes|required|in:free,paid',
            'price' => 'sometimes|required|numeric',
            'final_price' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|in:published,draft',
        ];
    }
}
