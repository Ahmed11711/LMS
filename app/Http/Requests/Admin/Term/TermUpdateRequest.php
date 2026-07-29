<?php

namespace App\Http\Requests\Admin\Term;
use App\Http\Requests\BaseRequest\BaseRequest;
class TermUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'academic_year_id' => 'nullable|sometimes|integer|exists:academic_years,id',
        ];
    }
}
