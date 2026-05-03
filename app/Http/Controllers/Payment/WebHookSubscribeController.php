<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\webhook\KashierWebhookUserSubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebHookSubscribeController extends Controller
{
    public function __construct(
        private readonly KashierWebhookUserSubService $webhookService
    ) {}

    public function handle(Request $request, string $id): JsonResponse
    {
        $this->webhookService->handle($request->all(), $id);

        return response()->json(['message' => 'ok']);
    }
}
