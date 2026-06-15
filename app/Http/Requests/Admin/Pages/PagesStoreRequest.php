<?php

namespace App\Http\Requests\Admin\Pages;

use App\Http\Requests\BaseRequest\BaseRequest;

class PagesStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|unique:pages,title|string|max:255',
            'status' => 'required|string|max:255',
        ];
    }
}
