<?php

namespace App\Http\Requests\Admin\LandingPage;
use App\Http\Requests\BaseRequest\BaseRequest;
class LandingPageStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_name' => 'required|string|max:255',
            'content' => 'required|array',
            'is_active' => 'required|boolean',
            'course_id' => 'required|integer|exists:courses,id',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}
