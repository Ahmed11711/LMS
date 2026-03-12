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

        // محاولة جلب القيمة من كل المفاتيح الممكنة
        $contact = $request->input('contact')
            ?? $request->input('phone')
            ?? $request->input('email');

        // إذا كانت القيمة "phone" أو "email" ككلمة نصية، نتخطاها للجلب الفعلي
        if ($contact === 'phone' || $contact === 'email') {
            $contact = $request->input('phone') ?? $request->input('email');
        }

        // تأكد أن $contact ليس فارغاً قبل بناء المفتاح
        if (!$contact) {
            return $this->errorResponse('Contact field (email or phone) is required to verify OTP', 422);
        }

        $userOtp = $request->input('otp');
        $tenant = app('tenant');
        $cacheKey = "otp_tenant_{$tenant->id}_{$contact}";

        $storedOtp = Cache::get($cacheKey);

        // الـ Log الجديد سيظهر لك القيمة التي تم استخراجها فعلياً
        Log::info("OTP CHECK - Final Contact: [{$contact}] | Key: {$cacheKey} | Stored: {$storedOtp}");

        if (!$storedOtp || (string)$storedOtp !== (string)$userOtp) {
            return $this->errorResponse('code is expired or invalid', 422);
        }

        // بقية الكود (البحث عن اليوزر وتحديثه)...
        $user = User::where('email', $contact)->orWhere('phone', $contact)->first();
        if ($user) {
            $user->forceFill(['email_verified_at' => now()])->save();
            Cache::forget($cacheKey);
            return $this->successResponse(['message' => 'OTP verified successfully', 'user' => $user]);
        }

        return $this->errorResponse('User not found', 404);
    }
}
