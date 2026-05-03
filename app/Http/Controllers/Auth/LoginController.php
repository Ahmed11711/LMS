<?php

namespace App\Http\Controllers\Auth;

use \App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateAccountRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Repositories\User\UserRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public UserRepositoryInterface $userRepository) {}
    public function login(LoginRequest $request)
    {
        $data = $request->validated();


        $contact = $request->input('email') ?? $request->input('phone');

        $user = User::where('email', $contact)
            ->orWhere('phone', $contact)
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        // ✅ Clear any stale/expired token from the incoming request
        // JWTAuth::unsetToken();

        $token = JWTAuth::claims([
            'tenant_id' => app('tenant')->id,
        ])->fromUser($user);

        return (new LoginResource($user))->additional([
            'meta' => [
                'access_token' => $token,
                'token_type'   => 'bearer',
            ]
        ]);
    }

    public function register(CreateAccountRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role'     => 'student',
        ]);
        JWTAuth::unsetToken();

        $token = JWTAuth::claims([
            'tenant_id' => app('tenant')->id
        ])->fromUser($user);

        return (new LoginResource($user))->additional([
            'meta' => [
                'access_token' => $token,
                'token_type'   => 'bearer',
            ]
        ]);
    }
}
