<?php

namespace App\Http\Controllers\Admin\Auth;

use \App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OTP\CheckOtpRequest;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckOtpController extends Controller
{
    use ApiResponseTrait;


    public function checkOtp(CheckOtpRequest $request)
    {
        $request->validated();
        $contact = $request->input('contact');
        $userOtp = $request->input('otp');

        $tenant = app('tenant');
        $cacheKey = "otp_tenant_{$tenant->id}_{$contact}";

        $storedOtp = Cache::get($cacheKey);

        if (!$storedOtp || $storedOtp != $userOtp) {
            return $this->errorResponse('code is expired or invalid', 422);
        }


        $user = User::where('email', $contact)
            ->orWhere('phone', $contact)
            ->first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => Carbon::now(),
            ])->save();

            Cache::forget($cacheKey);

            return $this->successResponse([
                'message' => 'OTP verified successfully',
                'user' => $user
            ]);
        }

        return $this->errorResponse('User not found in this academy', 404);
    }
}
