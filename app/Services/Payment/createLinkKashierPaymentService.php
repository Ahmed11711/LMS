<?php

namespace App\Services\Payment;

use App\Models\Central\User;
use App\Repositories\Package\PackageRepositoryInterface;
use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class CreateLinkKashierPaymentService
{
    public function __construct(
        private KashierPaymentService $kashierPaymentService,
        private PackageRepositoryInterface $packageRepository,
        private UserPackageRepositoryInterface $userPackageRepository,
    ) {}

    public function createSession(array $data): string
    {
        return DB::transaction(function () use ($data) {

            $user = $this->resolveUser($data);

            $package = $this->packageRepository->find($data['package_id']);

            if (!$package) {
                throw new RuntimeException('Package not found.');
            }

            // 3️⃣ Generate Transaction ID
            $transactionId = (string) Str::uuid();

            // 4️⃣ Create Kashier Session
            $paymentLink = $this->kashierPaymentService->createSession(
                amount: (string) $package->price,
                customerEmail: $user->email,
                transactionId: $transactionId,
            );

            if (!$paymentLink) {
                throw new RuntimeException('Failed to create payment session.');
            }

            // 5️⃣ Create Pending Subscription (NO activation yet)
            $this->userPackageRepository->create([
                'user_id'        => $user->id,
                'package_id'     => $package->id,
                'transaction_id' => $transactionId,
                'price'          => $package->price,
                'start_date'     => null, // activate after webhook
                'end_date'       => null,
                'status'         => 'pending',
            ]);

            return $paymentLink;
        });
    }

    /**
     * Resolve user by email or phone
     */
    private function resolveUser(array $data)
    {
        $userModel = (new User)->setConnection(config('database.default'));

        if (!empty($data['email'])) {
            $user = $userModel->where('email', $data['email'])->first();
            if ($user) return $user;
        }

        if (!empty($data['phone'])) {
            $user = $userModel->where('phone', $data['phone'])->first();
            if ($user) return $user;
        }

        throw new \RuntimeException('User not found.');
    }
    public function updateSubscriptionStatus(string $transactionId, string $status): void
    {
        $userPackage = $this->userPackageRepository->findBYKey('transaction_id', $transactionId);

        if (!$userPackage) {
            throw new \RuntimeException('Subscription not found for transaction ID: ' . $transactionId);
        }

        $package = $this->packageRepository->find($userPackage->package_id);

        if (!$package) {
            throw new \RuntimeException('Package not found for this subscription.');
        }

        DB::transaction(function () use ($userPackage, $package, $status) {

            if (strtoupper($status) === 'SUCCESS') {

                $this->userPackageRepository->query()
                    ->where('user_id', $userPackage->user_id)
                    ->where('id', '<>', $userPackage->id)
                    ->where('status', 'active')
                    ->update(['status' => 'cancelled']);

                $userPackage->update([
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addDays($package->duration_months * 30)
                ]);
            } else {
                $userPackage->update(['status' => 'failed']);
            }
        });
    }
}
