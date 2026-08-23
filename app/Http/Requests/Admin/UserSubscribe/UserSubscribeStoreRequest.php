<?php

namespace App\Http\Requests\Admin\UserSubscribe;

use App\Http\Requests\BaseRequest\BaseRequest;

class UserSubscribeStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'course_id' => 'required|integer|exists:courses,id',
            'starts_at' => 'required|date_format:Y-m-d',
            'status' => 'required|in:active,refunded,cancelled,pending',
            'message' => 'nullable|string|max:255',
        ];
    }
}
