<?php

namespace App\Http\Requests\Admin\UserSubscribe;

use App\Http\Requests\BaseRequest\BaseRequest;

class UserSubscribeUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'course_id' => 'sometimes|required|integer|exists:courses,id',
            'starts_at' => 'sometimes|date_format:Y-m-d',
            'status' => 'sometimes|required|in:active,refunded,cancelled,pending,completed',
            'message' => 'nullable|string|max:255',

        ];
    }
}
