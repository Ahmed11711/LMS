<?php

namespace App\Http\Requests\Admin\Setting;
use App\Http\Requests\BaseRequest\BaseRequest;
class SettingUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'sometimes|required|string|max:255|unique:settings,key,'.$this->route('setting').',id',
            'value' => 'nullable|sometimes|string',
        ];
    }
}
