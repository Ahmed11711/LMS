<?php

namespace App\Http\Requests\Admin\Grade;
use App\Http\Requests\BaseRequest\BaseRequest;
class GradeStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
