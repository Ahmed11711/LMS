<?php

namespace App\Http\Requests\Admin\PhysicalCourseDetail;
use App\Http\Requests\BaseRequest\BaseRequest;
class PhysicalCourseDetailStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'address' => 'required|string|max:255',
            'map_url' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'attachment' => 'nullable|string|max:255|file|max:2048',
        ];
    }
}
