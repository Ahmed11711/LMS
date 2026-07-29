<?php

namespace App\Http\Requests\Admin\AcademicYear;
use App\Http\Requests\BaseRequest\BaseRequest;
class AcademicYearStoreRequest extends BaseRequest
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
