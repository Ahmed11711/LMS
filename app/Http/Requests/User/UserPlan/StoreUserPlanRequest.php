<?php

namespace App\Http\Requests\User\UserPlan;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserPlanRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'plan_id' => 'required|integer|exists:plans,id',

        ];
    }
}
