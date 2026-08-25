<?php

namespace App\Services\TenantService;

use App\Models\Central\User;
use App\Repositories\Package\PackageRepositoryInterface;
use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateAcademyInfoService
{
    public function __construct(
        public TenantService $service,
        public UserPackageRepositoryInterface $userPackageRepository,
        public PackageRepositoryInterface $packageRepository
    ) {}

    public function registerAcademyTenant(array $data)
    {
        // Step 1: Commit Central DB Data first
        $packageData = DB::transaction(function () use ($data) {
            $user = User::where(function ($query) use ($data) {
                if (!empty($data['email'])) {
                    $query->where('email', $data['email']);
                }
                if (!empty($data['phone'])) {
                    $query->orWhere('phone', $data['phone']);
                }
            })->firstOrFail();

            $user->update([
                'username'      => $data['username'],
                'phone_academy' => $data['phone_academy'] ?? null,
                'country_code'  => $data['country_code'],
                'specialties'   => $data['specialties'],
                'type'          => $data['type'],
            ]);

            $userPackage = $this->assignFreePackage($user);

            $features = DB::connection('LMS_CENTER')
                ->table('feature_packages')
                ->join('features', 'feature_packages.feature_id', '=', 'features.id')
                ->where('feature_packages.package_id', $userPackage->package_id)
                ->select('features.key', 'feature_packages.value')
                ->get();

            return [
                'user'         => $user,
                'userPackage'  => $userPackage,
                'features'     => $features
            ];
        });

        // Step 2: Prepare Data for Tenant
        $user = $packageData['user'];

        $tenantData = array_merge($data, [
            'name'            => $user->username,
            'domain'          => $data['link_academy'],
            'user_name'       => $user->username,
            'phone_academy' => $data['phone_academy'] ?? null,
            'country_code'    => $user->country_code,
            'specialties'     => $user->specialties,
            'user_id'         => $user->id,
            'password'        => $user->password,
            'passed_package'  => $packageData['userPackage'],
            'passed_features' => $packageData['features'],
        ]);
        // Step 3: Run Tenant Creation
        try {
            $this->service->createTenant($tenantData);
        } catch (\Exception $e) {
            Log::error("Failed to create tenant: " . $e->getMessage());
            throw $e;
        }

        return $packageData['user'];
    }

    public function assignFreePackage(User $user)
    {
        $freePackage = $this->packageRepository->getFreePackage();

        if (!$freePackage) {
            throw new \Exception('No free package available in the system.');
        }

        return $this->userPackageRepository->create([
            'user_id'        => $user->id,
            'package_id'     => $freePackage->id,
            'package_name'   => $freePackage->title,
            'active'         => true,
            'start_date'     => now(),
            'end_date' => now()->addDays((float)($freePackage->duration_months ?? 1) * 30),
            'price'          => 0,
            'transaction_id' => 'free-package-' . $user->id . '-' . time(),
        ]);
    }
}
