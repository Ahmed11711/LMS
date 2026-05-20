<?php

namespace App\Http\Requests\Admin\UserPlan;

use App\Http\Requests\BaseRequest\BaseRequest;


class UserPlanUpdateRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'status' => 'required|in:active,rejected'
        ];
    }
}
