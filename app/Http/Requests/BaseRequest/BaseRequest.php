<?php

namespace App\Http\Requests\BaseRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class BaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
      */
    protected function getSchema(): array
    {
        $schema = [];
        $rules = method_exists($this, 'rules') ? $this->rules() : [];

        foreach ($rules as $field => $rule) {
            $ruleArray = is_array($rule) ? $rule : explode('|', $rule);

            $schema[$field] = [
                'type'     => $this->detectType($ruleArray),
                'required' => in_array('required', $ruleArray),
                'label'    => Str::title(str_replace(['_id', '_'], ['', ' '], $field)),
            ];

             if (Str::endsWith($field, '_id')) {
                $resourceName = Str::plural(Str::replaceLast('_id', '', $field));
                $schema[$field]['type'] = 'select';

                 $displayField = 'name'; // default
                foreach ($ruleArray as $r) {
                    if (Str::startsWith($r, 'display_field:')) {
                        $displayField = Str::after($r, 'display_field:');
                    }
                }

                 $fields = "id,{$displayField}";
                $schema[$field]['api'] = "/admin/" . Str::snake($resourceName) . "?fields={$fields}";

                $schema[$field]['allowed_fields'] = ['id', $displayField];
                $schema[$field]['display_column'] = $displayField;
            }

            foreach ($ruleArray as $singleRule) {
                if (Str::startsWith($singleRule, 'max:')) {
                    $schema[$field]['max'] = (int) Str::after($singleRule, 'max:');
                }
                if (Str::startsWith($singleRule, 'in:')) {
                    $schema[$field]['options'] = explode(',', Str::after($singleRule, 'in:'));
                }
            }
        }

        return $schema;
    }

    /**
      */
    private function detectType(array $rules): string
    {
        return match (true) {
            in_array('integer', $rules) => 'number',
            in_array('boolean', $rules) => 'boolean',
            in_array('date', $rules)    => 'date',
            in_array('file', $rules) || in_array('image', $rules) => 'file',
            in_array('array', $rules)   => 'array',
            default => 'string',
        };
    }


    /**
      */
    protected function getOptionalFields(): array
    {
        $optional = [];
        $rules = method_exists($this, 'rules') ? $this->rules() : [];

        foreach ($rules as $field => $rule) {
            $ruleArray = is_array($rule) ? $rule : explode('|', $rule);
            if (in_array('nullable', $ruleArray) || in_array('sometimes', $ruleArray)) {
                $optional[] = $field;
            }
        }
        return $optional;
    }

    /**
      */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $fullSchema = $this->getSchema();

         $filteredSchema = array_intersect_key($fullSchema, $errors);

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors detected.',
            'errors'  => $validator->errors(),
            'meta'    => [
                'schema'          => $filteredSchema, // هنا السر: هيرجع بس اللي ناقص
                'optional_fields' => $this->getOptionalFields(),
            ]
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
