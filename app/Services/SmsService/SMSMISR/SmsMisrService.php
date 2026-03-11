<?php

namespace App\Services\SmsService\SMSMISR;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsMisrService
{
    public function sendSms($mobile, $message)
    {
        $url = "https://smsmisr.com/api/SMS";

        // $response = Http::asForm()->post($url, [
        //     "environment" => "1", // 1 for production, 0 for testing
        //     "username"    => "d11beea6-3569-46ba-beed-4ace835e0a1e",
        //     "password"    => "4b8cd6c8273cc9a911e779e2ea981e9091f77c9c8b2571c5fc347de1579b09ff",
        //     "sender"      => "tiqnia",
        //     "mobile"      => $mobile,
        //     "language"    => "2", //  Arabic
        //     "message"     => $message,
        // ]);

        $response = Http::asForm()->post($url, [
            "environment" => "1", // وضع التشغيل الحقيقي
            "username"    => "d11beea6-3569-46ba-beed-4ace835e0a1e",
            "password"    => "4b8cd6c8273cc9a911e779e2ea981e9091f77c9c8b2571c5fc347de1579b09ff",

            // استخدم الـ Token الخاص بـ TIQNIA اللي ظهر عندك في الجدول
            "sender"      => "9f2f0802ec90da7d1a2c15c1ca6b6eaf008d92adf4e5d248a79435d2b61cbf57",

            "mobile"      => $mobile,
            "language"    => "2",
            "message"     => $message,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("SMS Misr Error: " . $response->body());
        return ["status" => "error", "message" => "Failed to send SMS"];
    }
}
