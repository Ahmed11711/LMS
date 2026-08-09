<?php

namespace App\Http\Requests\User\UserSubscribe;

use App\Http\Requests\BaseRequest\BaseRequest;
use App\Models\Course;
use App\Models\InstructorReceiverAccount;
use Illuminate\Validation\Rule;

class StoreUserSubscribeRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'receiver_account_id' => [
                'required',
                'integer',
                Rule::exists('instructor_receiver_accounts', 'id')
                    ->where('is_active', true),
            ],
            'receipt' => 'required|image',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $course = Course::find($this->course_id);
            $account = InstructorReceiverAccount::find($this->receiver_account_id);

            if ($course && $account && $account->user_id !== $course->user_id) {
                $validator->errors()->add(
                    'receiver_account_id',
                    'This receiver account does not belong to the course instructor.'
                );
            }
        });
    }
}
