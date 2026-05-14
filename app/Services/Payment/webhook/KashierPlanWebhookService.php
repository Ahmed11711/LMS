<?php

namespace App\Services\Payment\webhook;

use App\Models\UserPlan;
use Illuminate\Http\Request;

class KashierPlanWebhookController
{
    public function handle(Request $request, string $tenantHash)
    {
        $transactionId = $request->input('merchantOrderId');
        $status        = $request->input('paymentStatus'); // SUCCESS / FAILED

        $userPlan = UserPlan::where('transaction_id', $transactionId)->first();

        if (!$userPlan) return response()->json(['message' => 'not found'], 404);

        if ($status === 'SUCCESS') {
            $userPlan->update(['status' => 'active']);
        } elseif ($status === 'FAILED') {
            $userPlan->update(['status' => 'cancelled']);
        }

        return response()->json(['message' => 'ok']);
    }
}
