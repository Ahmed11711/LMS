<?php

namespace App\Http\Requests\Admin\Setting;

use App\Http\Requests\BaseRequest\BaseRequest;

class SettingStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'required|string',
        ];
    }
}
