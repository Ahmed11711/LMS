<?php

namespace App\Http\Requests\Admin\Plan;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class PlanStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'desc'    => 'required|string',
            'price'          => 'required|numeric|min:0',
            'duration_value' => 'required|integer|min:1',
            'duration_unit'  => 'required|in:days,months,years|required_with:duration_value',

            // rules array
            'rules'              => 'required|array',
            'rules.*.type'       => 'required_with:rules|in:all,instructor,category,course',
            'rules.*.reference_id' => 'nullable|integer',
        ];
    }

    protected function prepareForValidation(): void
    {
        // لو اختار all → يضيف rule تلقائي من غير ما الـ frontend يبعته
        if ($this->input('scope') === 'all') {
            $this->merge([
                'rules' => [['type' => 'all', 'reference_id' => null]]
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'rules.*.type.in' => 'The selected rule type is invalid.',
            'rules.*.type.required_with' => 'The rule type field is required.',
        ];
    }
}
