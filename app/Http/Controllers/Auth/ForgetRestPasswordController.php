<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgetRestPasswordController extends Controller
{

    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $data = $request->validated();
        $contact = $request->input('email') ?? $request->input('phone');

        $user = User::where('email', $contact)
            ->orWhere('phone', $contact)
            ->first();

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        // امسح أي OTP قديم لنفس الـ contact
        PasswordResetOtp::where('contact', $contact)->delete();

        $otp = random_int(100000, 999999); // كود من 6 أرقام

        PasswordResetOtp::create([
            'contact'    => $contact,
            'otp'        => Hash::make($otp), // نخزنه مشفر عشان الأمان
            'expires_at' => now()->addMinutes(10),
        ]);

        // TODO: ابعت الـ OTP فعلياً
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            // Mail::to($contact)->send(new OtpMail($otp));
        } else {
            // ابعته عن طريق SMS gateway بتاعك
        }

        return $this->successResponse([
            'message' => 'OTP sent successfully',
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $data = $request->validated();
        $contact = $request->input('email') ?? $request->input('phone');

        $record = PasswordResetOtp::where('contact', $contact)->first();

        if (!$record || $record->expires_at->isPast() || !Hash::check($data['otp'], $record->otp)) {
            return $this->errorResponse('Invalid or expired OTP', 400);
        }

        return $this->successResponse([
            'message' => 'OTP verified successfully',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();
        $contact = $request->input('email') ?? $request->input('phone');

        $record = PasswordResetOtp::where('contact', $contact)->first();

        if (!$record || $record->expires_at->isPast() || !Hash::check($data['otp'], $record->otp)) {
            return $this->errorResponse('Invalid or expired OTP', 400);
        }

        $user = User::where('email', $contact)
            ->orWhere('phone', $contact)
            ->first();

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        $record->delete();

        return $this->successResponse([
            'message' => 'Password reset successfully',
        ]);
    }
}
