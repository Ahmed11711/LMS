<?php

namespace App\Http\Controllers\Admin\Auth;

use \App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OTP\CheckOtpRequest;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckOtpController extends Controller
{
    use ApiResponseTrait;


    public function checkOtp(CheckOtpRequest $request)
    {
        $request->validated();

        // تعديل لجلب القيمة الصحيحة: إذا كانت كلمة 'phone' ابحث عن الرقم في الحقول الأخرى
        $contact = $request->input('contact');
        if ($contact === 'phone' || $contact === 'email') {
            $contact = $request->input('phone') ?? $request->input('email');
        }

        $userOtp = $request->input('otp');
        $tenant = app('tenant');
        $cacheKey = "otp_tenant_{$tenant->id}_{$contact}";

        $storedOtp = Cache::get($cacheKey);

        // Log للتأكد من القيمة بعد التعديل
        Log::info("Checking Key: " . $cacheKey . " | Stored: " . $storedOtp . " | Entered: " . $userOtp);

        if (!$storedOtp || (string)$storedOtp !== (string)$userOtp) {
            return $this->errorResponse('code is expired or invalid', 422);
        }

        // البحث عن المستخدم
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
