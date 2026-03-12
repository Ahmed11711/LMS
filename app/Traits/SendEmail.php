<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

trait SendEmail
{
    /**
     */
    private function sendViaBrevo($toEmail, $subject, $viewName, $data, $toName = 'Recipient')
    {
        Log::info("📧 Brevo Process Started: Sending '$subject' to $toEmail");

        try {
            $client = new Client();
            $htmlContent = View::make($viewName, $data)->render();

            $payload = [
                'sender' => [
                    'name'  => 'zayamrock.com',
                    'email' => 'dohakhaledeldbes@gmail.com' // يفضل وضعها في ملف .env
                ],
                'to' => [
                    [
                        'email' => $toEmail,
                        'name'  => $toName
                    ]
                ],
                'subject'     => $subject,
                'htmlContent' => $htmlContent,
            ];

            $response = $client->post('https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'accept'       => 'application/json',
                    'api-key'      => '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',
                    'content-type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            Log::info('✅ Brevo sent successfully', ['status' => $response->getStatusCode()]);
            return $response->getBody()->getContents();
        } catch (ClientException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            Log::error('❌ Brevo Client Error', ['error' => $errorBody]);
            return "Error: " . $errorBody;
        } catch (\Exception $e) {
            Log::critical('🔥 General Error in Brevo Trait', ['message' => $e->getMessage()]);
            return "Error: " . $e->getMessage();
        }
    }

    // --- الوظائف المساعدة التي تستخدم الوظيفة الرئيسية ---

    public function generateOTP()
    {
        return rand(100000, 999999);
    }

    public function sendEmail($to, $otp)
    {
        return $this->sendViaBrevo($to, 'zayamrock - OTP Verification', 'otpmail', ['otpData' => $otp]);
    }

    public function sendEmailToAdmin($formData, $images, $user)
    {
        $adminEmail = 'ahmedsamir11711@gmail.com';
        $data = [
            'formData' => $formData,
            'images'   => $images,
            'user'     => $user
        ];
        return $this->sendViaBrevo($adminEmail, "New Form Submission", 'sendForm', $data, 'Security Admin');
    }

    public function autoReplay($to, $message)
    {
        return $this->sendViaBrevo($to, "zayamrock - Response", 'otpmail', ['otpData' => $message]);
    }
}
