<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KashierPaymentPlanService
{
    public function createSession(
        string $amount,
        string $customerContact,
        string $transactionId,
        ?string $tenantId = null,
    ): ?string {
        $isEmail    = filter_var($customerContact, FILTER_VALIDATE_EMAIL);
        $queryParam = $isEmail ? 'email=' : 'phone=';

        $webhookUrl = rtrim(config('app.url'), '/')
            . '/kashier/webhook/academy/'
            . rtrim(strtr(base64_encode($tenantId ?? 'default'), '+/', '-_'), '=')
            . '/plan';
        $tenantBaseUrl = app()->environment('local')
            ? rtrim(config('app.url'), '/')
            : 'https://' . rtrim($tenantId, '/');

        $payload = [
            'expireAt'           => now()->addMinutes(30)->toISOString(),
            'maxFailureAttempts' => 3,
            'amount'             => $amount,
            'currency'           => 'EGP',
            'order'              => $transactionId,
            'merchantId'         => 'MID-41016-213',
            'merchantRedirect'   => $tenantBaseUrl . '/user/plans?' . $queryParam . urlencode($customerContact),
            'failureRedirect'    => true,
            'serverWebhook'      => $webhookUrl,
            'allowedMethods'     => 'card,wallet',
            'interactionSource'  => 'ECOMMERCE',
            'enable3DS'          => true,
            'customer' => [
                'email'     => $isEmail ? $customerContact : $customerContact . "@mobile.academy",
                'reference' => 'CUST-' . \Illuminate\Support\Str::uuid(),
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'df974d751303a6d76a5637d19ca9a0f7$2c9243f4284be65f2055d390c1185f2fac0619b8c7a4ffee04af37e48051409836beda2dd93ebb72988ef55ad0d8e4ea',
                'api-key'       => '9f78bd9d-fd4e-45fd-a7a6-93e3998b8712',
                'Content-Type'  => 'application/json',
            ])->post('https://test-api.kashier.io/v3/payment/sessions', $payload);

            if ($response->successful()) {
                $sessionUrl = $response->json('sessionUrl');
                if ($sessionUrl) {
                    Log::info('Kashier plan session created', [
                        'url'         => $sessionUrl,
                        'webhook_url' => $webhookUrl,
                    ]);
                    return $sessionUrl;
                }
                Log::error('Kashier plan sessionUrl missing');
            } else {
                Log::error('Kashier plan API failed', ['body' => $response->body()]);
            }
            return null;
        } catch (\Throwable $e) {
            Log::error('Kashier plan exception', ['msg' => $e->getMessage()]);
            return null;
        }
    }
}
