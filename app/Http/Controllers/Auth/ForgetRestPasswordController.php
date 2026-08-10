<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Services\SmsService\SMSMISR\SmsMisrService;
use App\Traits\ApiResponseTrait;
use App\Traits\SendEmail;
use Illuminate\Support\Facades\Hash;

class ForgetRestPasswordController extends Controller
{
    use ApiResponseTrait, SendEmail;

    public function __construct(protected SmsMisrService $smsService) {}

    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $contact = $request->input('email') ?? $request->input('phone');

        $user = User::where('email', $contact)
            ->orWhere('phone', $contact)
            ->first();

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        PasswordResetOtp::where('contact', $contact)->delete();

        $otp = $this->generateOTP();

        PasswordResetOtp::create([
            'contact'    => $contact,
            'otp'        => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $result = $this->sendEmail($contact, $otp);

            if ($result !== true) {
                return $this->errorResponse('Failed to send OTP email', 500);
            }
        } elseif ($this->isEgyptianPhone($contact)) {
            $message = "كود التحقق الخاص بك هو: {$otp}";
            $smsResult = $this->smsService->sendSms($this->normalizeEgyptianPhone($contact), $message);

            if (($smsResult['status'] ?? null) === 'error') {
                return $this->errorResponse('Failed to send OTP SMS', 500);
            }
        } else {
            return $this->errorResponse('Unsupported contact method', 422);
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

    /**
     * تحقق إن الرقم رقم مصري صحيح (01xxxxxxxxx أو +201xxxxxxxxx أو 00201xxxxxxxxx)
     */
    private function isEgyptianPhone(string $phone): bool
    {
        return (bool) preg_match('/^(?:\+20|0020|0)?1[0125]\d{8}$/', $phone);
    }

    /**
     * توحيد صيغة الرقم قبل إرساله للـ SMS Gateway (بيرجع بصيغة 201xxxxxxxxx)
     */
    private function normalizeEgyptianPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '0020')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '01')) {
            $digits = '20' . substr($digits, 1);
        } elseif (str_starts_with($digits, '1') && strlen($digits) === 10) {
            $digits = '20' . $digits;
        }

        return $digits;
    }
}
