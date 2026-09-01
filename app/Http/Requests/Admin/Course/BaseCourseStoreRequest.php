<?php

namespace App\Http\Requests\Admin\Course;

use App\Http\Requests\BaseRequest\BaseRequest;

abstract class BaseCourseStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:recorded,online,physical',
            'category_id' => 'nullable|integer|exists:categories,id',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'price_type'  => 'required|in:free,paid',
            'price'       => 'required|numeric|min:0',
            'final_price' => 'required|numeric|min:0',
            'status'      => 'required|in:published,draft',
            'currency'    => 'required|string|max:50',
            'completion_percentage' => 'nullable|numeric|min:0|max:100',

            'grade_id'         => 'nullable|integer|exists:grades,id',
            'term_id'          => 'nullable|integer|exists:terms,id',
            'subject_id'       => 'nullable|integer|exists:subjects,id',
            'academic_year_id' => 'nullable|integer|exists:academic_years,id',

            'infos'              => 'nullable|array',
            'infos.*.info_key'   => 'required_with:infos|string|max:255',
            'infos.*.info_value' => 'required_with:infos|string|max:255',
            'infos.*.order'      => 'nullable|integer|min:1',

            'receiver_accounts'   => 'nullable|array',
            'receiver_accounts.*' => 'integer|exists:instructor_receiver_accounts,id',

            'template_id' => 'nullable|exists:templates,id',
            'access_duration_type' => 'required|in:lifetime,until_date,days',
            'access_days'           => 'required_if:access_duration_type,days|nullable|integer|min:1',
            'access_until_date'     => 'required_if:access_duration_type,until_date|nullable|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'            => 'The course title is mandatory.',
            'title.string'              => 'The course title must be a string.',
            'title.max'                 => 'The course title must not exceed 255 characters.',

            'user_id.required'          => 'Please select an instructor for this course.',
            'user_id.integer'           => 'The instructor ID must be a valid integer.',
            'user_id.exists'            => 'The selected instructor does not exist in our records.',

            'type.required'             => 'You must specify the course type.',
            'type.in'                   => 'The course type must be: recorded, online, or physical.',

            'category_id.integer'       => 'The category ID must be a valid integer.',
            'category_id.exists'        => 'The selected category does not exist.',

            'description.required'      => 'The course description is required.',

            'image.image'               => 'The uploaded file must be an image.',
            'image.mimes'               => 'Only jpeg, png, jpg, and webp formats are allowed.',
            'image.max'                 => 'The image size must not exceed 2MB.',

            'price_type.required'       => 'Please specify the price type (free or paid).',
            'price_type.in'             => 'The price type must be either free or paid.',

            'price.required'            => 'The original price is required.',
            'price.numeric'             => 'The price must be a valid number.',
            'price.min'                 => 'The price must be at least 0.',

            'final_price.required'      => 'The final price is required.',
            'final_price.numeric'       => 'The final price must be a valid number.',
            'final_price.min'           => 'The final price must be at least 0.',

            'status.required'           => 'Please set the course status.',
            'status.in'                 => 'The status must be either published or draft.',

            'infos.array'               => 'The infos field must be an array.',
            'infos.*.key.required_with' => 'Each info item must have a key.',
            'infos.*.key.string'        => 'The info key must be a string.',
            'infos.*.key.max'           => 'The info key must not exceed 255 characters.',
            'infos.*.value.required_with' => 'Each info item must have a value.',
            'infos.*.value.string'      => 'The info value must be a string.',
            'infos.*.value.max'         => 'The info value must not exceed 255 characters.',
            'infos.*.order.integer'     => 'The info order must be a valid integer.',
            'infos.*.order.min'         => 'The info order must be at least 1.',

            'receiver_accounts.array'        => 'Receiver accounts must be a list.',
            'receiver_accounts.*.integer'    => 'Each receiver account ID must be a valid integer.',
            'receiver_accounts.*.exists'     => 'One or more selected receiver accounts do not exist.',

            'grade_id.exists'         => 'The selected grade does not exist.',
            'term_id.exists'          => 'The selected term does not exist.',
            'subject_id.exists'       => 'The selected subject does not exist.',
            'academic_year_id.exists' => 'The selected academic year does not exist.',
            'access_duration_type.required' => 'You must specify the access duration type.',
            'access_duration_type.in'       => 'Access duration type must be: lifetime, until_date, or days.',

            'access_days.required_if' => 'You must specify the number of access days.',
            'access_days.integer'     => 'Access days must be a valid integer.',
            'access_days.min'         => 'Access days must be at least 1.',

            'access_until_date.required_if' => 'You must specify the access end date.',
            'access_until_date.date'        => 'Access end date must be a valid date.',
            'access_until_date.after'       => 'Access end date must be a future date.',
        ];
    }
}
