<?php

namespace App\Http\Requests\Admin\Term;
use App\Http\Requests\BaseRequest\BaseRequest;
class TermStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'academic_year_id' => 'nullable|integer|exists:academic_years,id',
        ];
    }
}
