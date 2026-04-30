<?php

namespace App\Http\Requests\Admin\Course;

use App\Http\Requests\BaseRequest\BaseRequest;
class CourseStoreRequest extends BaseCourseStoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'user_id' => 'required|integer|exists:users,id',  
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'user_id.required' => 'Please select an instructor for this course.',
            'user_id.integer'  => 'The instructor ID must be a valid integer.',
            'user_id.exists'   => 'The selected instructor does not exist in our records.',
        ]);
    }
}