<?php

namespace App\Http\Requests\Admin\Pages;

use App\Http\Requests\BaseRequest\BaseRequest;

class PagesUpdateRequest extends BaseRequest
{


    public function rules(): array
    {
        return [
            'title' => 'sometimes|unique:pages,title|string|max:255',
            'status' => 'sometimes|required|string|max:255',
        ];
    }
}
