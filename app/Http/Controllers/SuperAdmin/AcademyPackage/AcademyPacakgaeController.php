<?php

namespace App\Http\Controllers\SuperAdmin\AcademyPackage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserPackage\UserPackageUpdateRequest;
use App\Models\Central\UserPackage;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AcademyPacakgaeController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $hasReceipt = Schema::hasColumn('user_packages', 'receipt');

        $packages = UserPackage::query()
            ->with('user:id,name,email')
            ->latest()
            ->paginate($perPage);

        $packages->getCollection()->transform(function ($package) use ($hasReceipt) {
            $package->receipt = $hasReceipt && $package->receipt
                ? asset('storage/' . ltrim($package->receipt, '/'))
                : 'https://placehold.co/400x300?text=Receipt'; // رابط تجريبي مؤقت

            return $package;
        });

        return $this->successResponsePaginate($packages, 'Packages fetched successfully');
    }

    public function update(UserPackageUpdateRequest $request, UserPackage $academyPackage)
    {
        $academyPackage->update($request->validated());

        return $this->successResponse($academyPackage, 'Package updated successfully');
    }
}
