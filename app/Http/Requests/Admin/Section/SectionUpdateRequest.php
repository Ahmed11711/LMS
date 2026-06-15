<?php

namespace App\Http\Requests\Admin\Section;
use App\Http\Requests\BaseRequest\BaseRequest;
class SectionUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pages_id' => 'sometimes|required|integer|exists:pages,id',
            'type' => 'sometimes|required|string|max:255',
            'order' => 'sometimes|required|integer',
            'props' => 'sometimes|required|array',
        ];
    }
}
