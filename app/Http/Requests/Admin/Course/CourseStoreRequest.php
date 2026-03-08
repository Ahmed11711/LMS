<?php

namespace App\Http\Requests\Admin\Course;

use App\Http\Requests\BaseRequest\BaseRequest;

class CourseStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'type' => 'required|in:recorded,online,physical',
            'category_id' => 'required|integer|exists:categories,id',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Corrected to image validation
            'price_type' => 'required|in:free,paid',
            'price' => 'required|numeric|min:0',
            'final_price' => 'required|numeric|min:0',
            'status' => 'required|in:published,draft',
        ];
    }

    /**
     * Custom error messages for validation
     */
    public function messages(): array
    {
        return [
            // Title
            'title.required' => 'The course title is mandatory.',

            // User/Instructor
            'user_id.required' => 'Please select an instructor for this course.',
            'user_id.exists' => 'The selected instructor does not exist in our records.',

            // Type
            'type.required' => 'You must specify the course type (recorded, online, or physical).',
            'type.in' => 'The selected course type is invalid.(recorded, online, or physical)',

            // Category
            'category_id.exists' => 'The selected category is invalid.',

            // Image
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Only jpeg, png, jpg, and webp formats are allowed.',
            'image.max' => 'The image size must not exceed 2MB.',

            // Pricing
            'price_type.in' => 'Price type must be either free or paid.',
            'price.required' => 'The original price field is required.',
            'final_price.required' => 'The final price field is required.',
            'price.numeric' => 'The price must be a valid number.',

            // Status
            'status.required' => 'Please set the course status.',
            'status.in' => 'The status must be either published or draft.',
        ];
    }
}
