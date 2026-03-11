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

        $response = Http::asForm()->post("https://smsmisr.com/api/SMS", [
            "environment" => "0", // 0 للتجربة
            "username"    => "d11beea6-3569-46ba-beed-4ace835e0a1e", // تأكد إنه الـ API Key من الموقع
            "password"    => "4b8cd6c8273cc9a911e779e2ea981e9091f77c9c8b2571c5fc347de1579b09ff",
            "sender"      => "b611afb996655a94c8e942a823f1421de42bf8335d24ba1f84c437b2ab11ca27", // الـ Sender الخاص بالتجربة
            "mobile"      => "201094321637",
            "language"    => "2",
            "message"     => "Test Message",
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("SMS Misr Error: " . $response->body());
        return ["status" => "error", "message" => "Failed to send SMS"];
    }
}
