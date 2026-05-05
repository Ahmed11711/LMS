<?php

namespace App\Services\Payment;

use App\Repositories\UserSubscribe\UserSubscribeRepository;
use App\Repositories\Course\CourseRepository;
use App\Services\Payment\KashierPaymentService;
use Illuminate\Support\Str;

class UserSubscribeService
{
    public function __construct(
        private UserSubscribeRepository $userSubscribeRepo,
        private CourseRepository $courseRepo,
        private KashierPaymentUserSubscribeService $kashierService,
    ) {}

    public function execute(int $userId, int $courseId, string $customerContact, ?string $tenantDomain  = null)
    {
        // 1. Check if already subscribed
        $alreadySubscribed = $this->userSubscribeRepo->isAlreadySubscribed($userId, $courseId);

        if ($alreadySubscribed) {
            return [
                'success' => false,
                'message' => 'You are already subscribed to this course',
            ];
        }

        // 2. Get course price
        $course = $this->courseRepo->find($courseId);

        // 3. Create transaction reference
        $transactionReference = 'TXN-' . Str::uuid();

        // 4. Save subscription as pending
        $subscription = $this->userSubscribeRepo->updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['status' => 'pending', 'transaction_id' => $transactionReference]
        );
        // 5. Create payment link
        $paymentUrl = $this->kashierService->createSession(
            amount: (string) $course->final_price,
            customerContact: $customerContact,
            transactionId: $transactionReference,
            tenantId: $tenantDomain,
        );

        if (!$paymentUrl) {
            // rollback the subscription
            $subscription->delete();

            return [
                'success' => false,
                'message' => 'Failed to create payment link',
            ];
        }

        return [
            'success'     => true,
            'payment_url' => $paymentUrl,
        ];
    }
}
