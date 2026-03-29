<?php

namespace App\Http\Requests\Admin\OnlineSession;
use App\Http\Requests\BaseRequest\BaseRequest;
class OnlineSessionUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'sometimes|required|integer|exists:courses,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|sometimes|string',
            'session_url' => 'sometimes|required|string|max:255',
            'notes' => 'nullable|sometimes|string|max:255',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required',
        ];
    }
}
