<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KashierPaymentService
{
    /**
     * Create Kashier payment session
     */
    public function createSession(
        string $amount,
        string $customerEmail,
        string $transactionId,

    ): ?string {


        $payload = [
            'expireAt' => now()->addMinutes(30)->toISOString(),
            'maxFailureAttempts' => 3,
            'amount' => $amount,
            'currency' => 'EGP',
            'order' => $transactionId,
            'merchantId' => 'MID-41016-213',
            'merchantRedirect' => config('app.url') . '/auth/setup',
            'failureRedirect' => true,
            'serverWebhook'    => config('app.url') . '/kashier/webhook',

            'allowedMethods' => 'card,wallet',
            'interactionSource' => 'ECOMMERCE',
            // 'email' => $customerEmail,
            'enable3DS' => true,

            'customer' => [
                'email'     => $customerEmail,
                'reference' => 'CUST-' . Str::uuid(),
            ],
        ];


        try {

            $response = Http::withHeaders([
                'Authorization' => 'df974d751303a6d76a5637d19ca9a0f7$2c9243f4284be65f2055d390c1185f2fac0619b8c7a4ffee04af37e48051409836beda2dd93ebb72988ef55ad0d8e4ea',
                'api-key'       => '9f78bd9d-fd4e-45fd-a7a6-93e3998b8712',
                'Content-Type'  => 'application/json',
            ])->post('https://test-api.kashier.io/v3/payment/sessions', $payload);

            Log::info('Kashier raw response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if (!$response->successful()) {

                Log::error('Kashier API Error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return null;
            }

            $sessionUrl = $response->json('sessionUrl');

            if (!$sessionUrl) {

                Log::error('Kashier sessionUrl missing', [
                    'response' => $response->json(),
                ]);

                return null;
            }

            Log::info('Kashier session created successfully', [
                'sessionUrl' => $sessionUrl,
            ]);

            return $sessionUrl;
        } catch (\Throwable $e) {

            Log::error('Kashier createSession exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
