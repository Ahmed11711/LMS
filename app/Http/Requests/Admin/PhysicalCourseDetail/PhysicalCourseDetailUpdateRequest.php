<?php

namespace App\Http\Requests\Admin\PhysicalCourseDetail;
use App\Http\Requests\BaseRequest\BaseRequest;
class PhysicalCourseDetailUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'sometimes|required|integer|exists:courses,id',
            'address' => 'sometimes|required|string|max:255',
            'map_url' => 'nullable|sometimes|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
            'attachment' => 'nullable|sometimes|string|max:255|file|max:2048',
        ];
    }
}
