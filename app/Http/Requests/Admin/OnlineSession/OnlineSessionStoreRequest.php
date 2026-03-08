<?php

namespace App\Http\Requests\Admin\OnlineSession;
use App\Http\Requests\BaseRequest\BaseRequest;
class OnlineSessionStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_url' => 'required|string|max:255',
            'notes' => 'nullable|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
        ];
    }
}
