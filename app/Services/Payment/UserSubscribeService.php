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
        ?int $receiverAccountId = null,
        string $createdBy = 'self',
        string $status = 'pending',
    ) {
        $alreadySubscribed = $this->userSubscribeRepo->isActiveOrPendingSubscription($userId, $courseId);
        if ($alreadySubscribed) {
            return [
                'success' => false,
                'message' => 'You are already subscribed to this course',
            ];
        }

        $course = $this->courseRepo->find($courseId);
        $transactionReference = 'TXN-' . Str::uuid();

        $receiptPath = $receipt ? $receipt->store('storage/uploads/receipts', 'public') : null;

        $this->userSubscribeRepo->updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            [
                'status'              => $status,
                'transaction_id'      => $transactionReference,
                'receipt'             => $receiptPath,
                'created_by'          => $createdBy,
                'price'               => $course->final_price,
                'receiver_account_id' => $receiverAccountId,
            ]
        );

        if (!$payment) {
            return [
                'success' => true,
                'message' => 'Your subscription is being processed and will be activated shortly.',
            ];
        }

        $result = $this->createPaymentUrl(
            course: $course,
            customerContact: $customerContact,
            transactionReference: $transactionReference,
            tenantDomain: $tenantDomain,
        );

        return $result; // كانت ناقصة قبل كده
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
