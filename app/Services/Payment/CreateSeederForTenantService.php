<?php

namespace App\Services\Payment;

use App\Models\Tenant;
use App\Models\Central\User;
use App\Models\Tenant\UserPackage as TenantUserPackage;
use App\Repositories\Package\PackageRepositoryInterface;
use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateLinkKashierPaymentService
{
    public function __construct(
        private KashierPaymentService $kashierPaymentService,
        private PackageRepositoryInterface $packageRepository,
        private UserPackageRepositoryInterface $userPackageRepository,
    ) {}

    public function updateSubscriptionStatus(string $transactionId, string $status): void
    {
        $userPackage = $this->userPackageRepository->findBYKey('transaction_id', $transactionId);

        if (!$userPackage) {
            throw new \RuntimeException('Subscription not found for transaction ID: ' . $transactionId);
        }

        $package = $this->packageRepository->find($userPackage->package_id);

        DB::transaction(function () use ($userPackage, $package, $status) {
            if (strtoupper($status) === 'SUCCESS') {

                $this->updateCentralDatabase($userPackage, $package);

                $this->syncToTenantDatabase($userPackage, $package);
            } else {
                $userPackage->update(['status' => 'failed']);
            }
        });
    }

    private function updateCentralDatabase($userPackage, $package)
    {
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
    }

    private function syncToTenantDatabase($userPackage, $package)
    {
        $user = User::find($userPackage->user_id);

        if ($user && $user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);

            if ($tenant) {
                Tenancy::initialize($tenant);
                $features = DB::connection('LMS_CENTER')
                    ->table('feature_packages')
                    ->join('features', 'feature_packages.feature_id', '=', 'features.id')
                    ->where('feature_packages.package_id', $package->id)
                    ->select('features.key', 'feature_packages.value')
                    ->get();

                DB::connection('tenant')->table('user_packages')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'package_id'   => $package->id,
                        'package_name' => $package->title ?? $package->name,
                        'status'       => 'active',
                        'price'        => $package->price,
                        'start_date'   => now(),
                        'end_date'     => now()->addDays($package->duration_months * 30),
                        'updated_at'   => now(),
                    ]
                );

                foreach ($features as $f) {
                    DB::connection('tenant')->table('tenant_feature_usage')->updateOrInsert(
                        ['feature_slug' => $f->key],
                        [
                            'total_limit'  => $f->value,
                            'used_amount'  => 0,
                            'type'         => ($f->value == -1 || (int)$f->value > 1) ? 'numeric' : 'boolean',
                            'is_enabled'   => $f->value != 0,
                            'updated_at'   => now(),
                        ]
                    );
                }

                tenancy()->end();
            }
        }
    }
}
