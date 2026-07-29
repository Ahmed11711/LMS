<?php

namespace App\Http\Requests\Admin\AcademicYear;
use App\Http\Requests\BaseRequest\BaseRequest;
class AcademicYearUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
        ];
    }
}
