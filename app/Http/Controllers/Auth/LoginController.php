<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Repositories\User\UserRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public UserRepositoryInterface $userRepository) {}
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = $this->userRepository->findByKey('email', $data['email']);

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $token = JWTAuth::fromUser($user);
        $token = JWTAuth::claims([
            'tenant_id' => app('tenant')->id
        ])->fromUser($user);

        return (new LoginResource($user))->additional([
            'meta' => [
                'access_token' => $token,
                'token_type' => 'bearer',
            ]
        ]);
    }
}
