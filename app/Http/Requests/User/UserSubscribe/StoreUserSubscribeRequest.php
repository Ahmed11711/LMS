<?php

namespace App\Http\Requests\User\UserSubscribe;

use App\Http\Requests\BaseRequest\BaseRequest;
use App\Models\Course;
use Illuminate\Validation\Rule;

class StoreUserSubscribeRequest extends BaseRequest
{
    public function rules(): array
    {
        $course = Course::find($this->input('course_id'));
        $isFree = $course && $course->price_type === 'free';

        return [
            'course_id' => ['required', 'exists:courses,id'],
            'receiver_account_id' => [
                $isFree ? 'nullable' : 'required',
                'integer',
                Rule::exists('instructor_receiver_accounts', 'id')
                    ->where('is_active', true),
            ],
            'receipt' => [
                $isFree ? 'nullable' : 'required',
                'image',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required' => 'لازم ترفع صورة الإيصال.',
            'receiver_account_id.required' => 'لازم تختار وسيلة الدفع.',
        ];
    }
}
