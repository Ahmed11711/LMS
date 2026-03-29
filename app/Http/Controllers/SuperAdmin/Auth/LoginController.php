<?php

namespace App\Http\Controllers\SuperAdmin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = Auth::guard('central')->attempt($credentials)) {
            $user = Auth::guard('central')->user();
            return response()->json([
                'message' => 'Central Login Successful',
                'user' => $user,
                'token' => $token
            ]);
        }

        return response()->json(['message' => 'Invalid Central Credentials'], 401);
    }
}
