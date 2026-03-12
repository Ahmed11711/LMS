<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OTP\sendOtpRequest;
use App\Services\SmsService\SMSMISR\SmsMisrService;
use App\Traits\ApiResponseTrait;
use App\Traits\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendOtpController extends Controller
{
    use ApiResponseTrait;
    use SendEmail;
    public function __construct(public SmsMisrService $smsservice) {}

    // public function sendOtp(sendOtpRequest $request)
    // {
    //     $request->validated();

    //     $contact = $request->input('contact');
    //     $otp = $this->generateOTP();

    //     $tenant = app('tenant');

    //     $cacheKey = "otp_tenant_{$tenant->id}_{$contact}";


    //     Cache::put($cacheKey, $otp, now()->addMinutes(5));
    //     Log::info("Cache Key Generated: " . $cacheKey);

    //     if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
    //         $this->sendEmail($contact, $otp);
    //         return $this->successResponse('OTP sent to your email');
    //     }

    //     $phone = preg_replace('/[^0-9]/', '', $contact);

    //     if ($this->isEgyptianNumber($phone)) {
    //         return $this->sendEgyptianSms($phone, $otp);
    //     } else {
    //         return $this->sendInternationalSms($phone, $otp);
    //     }
    // }

    public function sendOtp(sendOtpRequest $request)
    {
        $request->validated();

        $contact = $request->input('contact');
        if (!$contact || $contact === 'phone' || $contact === 'email') {
            $contact = $request->input('phone') ?? $request->input('email');
        }

        $otp = $this->generateOTP();
        $tenant = app('tenant');
        $cacheKey = "otp_tenant_{$tenant->id}_{$contact}";

        Cache::put($cacheKey, $otp, now()->addMinutes(5));
        Log::info("OTP STORED - Key: {$cacheKey} | OTP: {$otp}");

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $this->sendEmail($contact, $otp);
            return $this->successResponse('OTP sent to your email');
        }

        $phone = preg_replace('/[^0-9]/', '', $contact);
        return ($this->isEgyptianNumber($phone))
            ? $this->sendEgyptianSms($phone, $otp)
            : $this->sendInternationalSms($phone, $otp);
    }

    private function isEgyptianNumber($phone)
    {
        return str_starts_with($phone, '20') || str_starts_with($phone, '01');
    }

    private function sendEgyptianSms($phone, $otp)
    {
        $this->smsservice->sendSms($phone, "Your OTP code is: $otp");
        return $this->successResponse('OTP sent via local SMS provider');
    }

    private function sendInternationalSms($phone, $otp)
    {
        return $this->successResponse('OTP sent via international SMS provider');
    }
}
