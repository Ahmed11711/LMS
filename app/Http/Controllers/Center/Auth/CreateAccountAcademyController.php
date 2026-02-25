<?php

namespace App\Http\Controllers\Center\Auth;

use App\Enums\Central\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Auth\CreateAcademyRequest;
use App\Http\Service\Payment\KashierPaymentService;
use App\Models\Central\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CreateAccountAcademyController extends Controller
{

    use ApiResponseTrait;
    public function create(CreateAcademyRequest $request)
    {
        $data = $request->validated();
        $data['role'] = UserRole::ACADEMY->value;
        $user = User::create($data);


        return $this->successResponse($user, 'Academy account created successfully');
    }
}
