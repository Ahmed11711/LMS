<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateAccountRequest;
use App\Repositories\User\UserRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CreateAccountController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public UserRepositoryInterface $userRepositry) {}
    public function create(CreateAccountRequest $request)
    {
        $data = $request->validated();
        $user = $this->userRepositry->create($data);
        return $this->successResponse($user, 'Account created successfully');
    }
}
