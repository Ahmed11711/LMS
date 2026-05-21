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

    public function execute(
        int $userId,
        int $courseId,
        string $customerContact,
        bool $payment,
        ?string $tenantDomain = null,
        $receipt = null,
        string $createdBy = 'self',
        string $status = "penidng",
    ) {
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
        if ($receipt) {
            $receiptPath = $receipt->store('uploads/receipts', 'public');
        }
        $this->userSubscribeRepo->updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            [
                'status'         => $status,
                'transaction_id' => 'TXN-' . Str::uuid(),
                'receipt'        => $receiptPath ?? null,
                'created_by'     => $createdBy,
            ]
        );

        // 5. Create payment link
        if ($payment) {
            $result = $this->createPaymentUrl(
                course: $course,
                customerContact: $customerContact,
                transactionReference: $transactionReference,
                tenantDomain: $tenantDomain,
            );

            if (!$result['success']) {
                return $result;
            }
        } else {
            return [
                'success' => true,
                'message' => 'Your subscription is being processed and will be activated shortly.',
            ];
        }
    }

    protected function createPaymentUrl($course, string $customerContact, string $transactionReference, ?string $tenantDomain): array
    {
        $paymentUrl = $this->kashierService->createSession(
            amount: (string) $course->final_price,
            customerContact: $customerContact,
            transactionId: $transactionReference,
            tenantId: $tenantDomain,
        );

        if (!$paymentUrl) {
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

    public function getUserSubscribes(int $userId)
    {
        return $this->userSubscribeRepo->getUserSubscribes($userId);
    }
}
