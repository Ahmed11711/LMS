<?php

namespace App\Http\Requests\Admin\Section;

use App\Http\Requests\BaseRequest\BaseRequest;

class SectionStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pages_id'       => 'required|integer|exists:pages,id',
            'type'           => 'required|string|max:255',
            'order'          => 'required|integer|min:0',
            'props'          => 'required|array',

            'items'          => 'sometimes|array',
            'items.*.order'  => 'sometimes|integer|min:0',
            'items.*.props'  => 'required_with:items|array',
        ];
    }
}
