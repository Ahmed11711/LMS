<?php

namespace App\Http\Requests\Admin\LandingPage;
use App\Http\Requests\BaseRequest\BaseRequest;
class LandingPageUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|array',
            'is_active' => 'sometimes|required|boolean',
            'course_id' => 'sometimes|required|integer|exists:courses,id',
            'user_id' => 'sometimes|required|integer|exists:users,id',
        ];
    }
}
