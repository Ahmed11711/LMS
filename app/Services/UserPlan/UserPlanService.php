<?php

namespace App\Services\UserPlan;

use App\Models\UserPlan;
use App\Models\Plan;
use App\Services\Payment\KashierPaymentPlanService;
use Illuminate\Support\Str;

class UserPlanService
{
    public function __construct(
        private KashierPaymentPlanService $kashierService,
    ) {}

    public function execute(int $userId, int $planId, string $customerContact, ?string $tenantDomain = null): array
    {
        $plan = Plan::where('id', $planId)
            ->where('status', 'active')
            ->first();

        if (!$plan) {
            return ['success' => false, 'message' => 'الباقة غير متاحة'];
        }

        $hasActive = UserPlan::where('user_id', $userId)
            ->where('plan_id', $planId)
            ->where('status', 'active')
            ->exists();

        if ($hasActive) {
            return ['success' => false, 'message' => 'You already have an active subscription to this plan.'];
        }

        $transactionReference = 'PLAN-TXN-' . Str::uuid();

        $userPlan = UserPlan::create([
            'user_id'        => $userId,
            'plan_id'        => $planId,
            'starts_at'      => now(),
            'ends_at'        => $this->calcEndsAt($plan),
            'transaction_id' => $transactionReference,
            'amount_paid'    => $plan->price,
            'status'         => 'pending',
        ]);

        $paymentUrl = $this->kashierService->createSession(
            amount: (string) $plan->price,
            customerContact: $customerContact,
            transactionId: $transactionReference,
            tenantId: $tenantDomain,
        );

        if (!$paymentUrl) {
            $userPlan->delete();
            return ['success' => false, 'message' => 'Failed to create payment link'];
        }

        return [
            'success'     => true,
            'payment_url' => $paymentUrl,
        ];
    }

    private function calcEndsAt(Plan $plan): ?string
    {
        if (!$plan->duration_value) return null; // lifetime

        return match ($plan->duration_unit) {
            'days'   => now()->addDays($plan->duration_value)->toDateTimeString(),
            'months' => now()->addMonths($plan->duration_value)->toDateTimeString(),
            'years'  => now()->addYears($plan->duration_value)->toDateTimeString(),
            default  => null,
        };
    }
}
