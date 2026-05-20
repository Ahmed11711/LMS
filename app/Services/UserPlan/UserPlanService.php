<?php

namespace App\Services\UserPlan;

use App\Repositories\Plan\PlanRepository;
use App\Repositories\UserPlan\UserPlanRepository;
use App\Services\Payment\KashierPaymentPlanService;
use Illuminate\Support\Str;

class UserPlanService
{
    public function __construct(
        private UserPlanRepository $userPlanRepo,
        private PlanRepository $planRepo,
        private KashierPaymentPlanService $kashierService,
    ) {}

    public function execute(
        int $userId,
        int $planId,
        string $customerContact,
        bool $payment = true,
        ?string $tenantDomain = null,
        $receipt = null,
        string $createdBy = 'self',
        string $status = 'pending',
    ): array {
        // 1. Check if already subscribed
        $hasActive = $this->userPlanRepo->isAlreadySubscribed($userId, $planId);

        if ($hasActive) {
            return ['success' => false, 'message' => 'You already have an active subscription to this plan.'];
        }

        // 2. Get plan
        $plan = $this->planRepo->findActive($planId);

        if (!$plan) {
            return ['success' => false, 'message' => 'The plan is not available'];
        }

        // 3. Create transaction reference
        $transactionReference = 'PLAN-TXN-' . Str::uuid();

        // 4. Save subscription as pending
        if ($receipt) {
            $receiptPath = $receipt->store('uploads/receipts', 'public');
        }

        $this->userPlanRepo->updateOrCreate(
            ['user_id' => $userId, 'plan_id' => $planId],
            [
                'starts_at'      => now(),
                'ends_at'        => $this->calcEndsAt($plan),
                'status'         => $status,
                'transaction_id' => $transactionReference,
                'amount_paid'    => $plan->price,
                'receipt'        => $receiptPath ?? null,
                'created_by'     => $createdBy,
            ]
        );

        // 5. Create payment link
        if ($payment) {
            $result = $this->createPaymentUrl(
                plan: $plan,
                customerContact: $customerContact,
                transactionReference: $transactionReference,
                tenantDomain: $tenantDomain,
            );

            if (!$result['success']) {
                return $result;
            }

            return $result;
        }

        return [
            'success' => true,
            'message' => 'Your subscription is being processed and will be activated shortly.',
        ];
    }

    protected function createPaymentUrl($plan, string $customerContact, string $transactionReference, ?string $tenantDomain): array
    {
        $paymentUrl = $this->kashierService->createSession(
            amount: (string) $plan->price,
            customerContact: $customerContact,
            transactionId: $transactionReference,
            tenantId: $tenantDomain,
        );

        if (!$paymentUrl) {
            return ['success' => false, 'message' => 'Failed to create payment link'];
        }

        return [
            'success'     => true,
            'payment_url' => $paymentUrl,
        ];
    }

    private function calcEndsAt($plan): ?string
    {
        if (!$plan->duration_value) return null;

        return match ($plan->duration_unit) {
            'days'   => now()->addDays($plan->duration_value)->toDateTimeString(),
            'months' => now()->addMonths($plan->duration_value)->toDateTimeString(),
            'years'  => now()->addYears($plan->duration_value)->toDateTimeString(),
            default  => null,
        };
    }
}
