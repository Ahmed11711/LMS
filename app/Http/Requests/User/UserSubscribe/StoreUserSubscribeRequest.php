<?php

namespace App\Http\Requests\User\UserSubscribe;

use App\Http\Requests\BaseRequest\BaseRequest;

class StoreUserSubscribeRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'course_id' => [
                'required',
                'exists:courses,id',
            ],
            'receipt' => 'required|image',

        ];
    }
}
