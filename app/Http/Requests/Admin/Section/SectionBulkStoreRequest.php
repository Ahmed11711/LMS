<?php

namespace App\Http\Requests\Admin\Section;

use App\Http\Requests\BaseRequest\BaseRequest;

class SectionBulkStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pages_id'   => 'required|integer|exists:pages,id',

            'sections'                  => 'required|array|min:1',
            'sections.*.type'           => 'required|string|max:255',
            'sections.*.order'          => 'required|integer|min:0',
            'sections.*.props'          => 'required|array',

            'sections.*.items'          => 'sometimes|array',
            'sections.*.items.*.order'  => 'sometimes|integer|min:0',
            'sections.*.items.*.props'  => 'required_with:sections.*.items|array',
        ];
    }
}
