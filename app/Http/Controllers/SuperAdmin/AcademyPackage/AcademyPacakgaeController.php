<?php

namespace App\Http\Controllers\SuperAdmin\AcademyPackage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserPackage\UserPackageUpdateRequest;
use App\Models\Central\UserPackage;
use App\Traits\ApiResponseTrait;
use App\QueryFilters\ColumnFilter;
use App\QueryFilters\Search;
use App\QueryFilters\SelectFields;
use App\QueryFilters\SortBy;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class AcademyPacakgaeController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $hasReceipt = Schema::hasColumn('user_packages', 'receipt');

        $query = UserPackage::query()->with('user:id,name,email');

        $packages = app(Pipeline::class)
            ->send($query)
            ->through([
                Search::class,
                ColumnFilter::class,
                SelectFields::class,
                SortBy::class,
            ])
            ->thenReturn()
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

    public function dashboard(Request $request)
    {
        $now = Carbon::now();

        $startOfThisMonth = $now->copy()->startOfMonth();
        $endOfThisMonth   = $now->copy()->endOfMonth();

        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth   = $now->copy()->subMonth()->endOfMonth();

        // 1) عدد الاشتراكات الجدد هذا الشهر
        $newSubsThisMonth = UserPackage::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $newSubsLastMonth = UserPackage::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        // 2) عدد الأكاديميات النشطة (باشتراك ساري)
        $activeAcademiesNow = UserPackage::where('ends_at', '>=', $now)
            ->distinct('user_id')->count('user_id');

        $activeAcademiesLastMonth = UserPackage::where('ends_at', '>=', $startOfThisMonth)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->distinct('user_id')->count('user_id');

        // 3) عدد الأكاديميات باشتراك منتهي
        $expiredAcademiesNow = UserPackage::where('ends_at', '<', $now)
            ->distinct('user_id')->count('user_id');

        $expiredAcademiesLastMonth = UserPackage::where('ends_at', '<', $startOfThisMonth)
            ->distinct('user_id')->count('user_id');

        // 4) اجمالي الايراد الحالي
        $revenueThisMonth = UserPackage::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->sum('price');
        $revenueLastMonth = UserPackage::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->sum('price');

        // 5) تفاصيل آخر 12 شهر (عدد الاشتراكات لكل شهر)
        $chart = UserPackage::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $data = [
            'new_subscriptions_this_month' => [
                'value'      => $newSubsThisMonth,
                'change_pct' => $this->percentageChange($newSubsThisMonth, $newSubsLastMonth),
            ],
            'active_academies' => [
                'value'      => $activeAcademiesNow,
                'change_pct' => $this->percentageChange($activeAcademiesNow, $activeAcademiesLastMonth),
            ],
            'expired_academies' => [
                'value'      => $expiredAcademiesNow,
                'change_pct' => $this->percentageChange($expiredAcademiesNow, $expiredAcademiesLastMonth),
            ],
            'total_revenue' => [
                'value'      => (float) $revenueThisMonth,
                'change_pct' => $this->percentageChange($revenueThisMonth, $revenueLastMonth),
            ],
            'chart_last_12_months' => $chart,
        ];

        return $this->successResponse($data, 'Dashboard data fetched successfully');
    }

    private function percentageChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
