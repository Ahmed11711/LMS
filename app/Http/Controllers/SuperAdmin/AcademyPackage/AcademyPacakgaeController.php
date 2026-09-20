<?php

namespace App\Http\Controllers\SuperAdmin\AcademyPackage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserPackage\UserPackageUpdateRequest;
use App\Models\Central\User;
use App\Models\Central\UserPackage;
use App\QueryFilters\ColumnFilter;
use App\QueryFilters\Search;
use App\QueryFilters\SelectFields;
use App\QueryFilters\SortBy;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademyPacakgaeController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        return User::get();
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
                ? asset(ltrim($package->payment_proof, '/'))
                : null;


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
        $data = Cache::remember('super_admin_dashboard_stats', now()->addMinutes(10), function () {
            return $this->buildDashboardStats();
        });

        return $this->successResponse($data, 'Dashboard data fetched successfully');
    }

    private function buildDashboardStats(): array
    {
        $now = Carbon::now();

        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth   = $now->copy()->subMonth()->endOfMonth();

        $stats = DB::table('user_packages')
            ->selectRaw("
            COUNT(CASE WHEN created_at >= ? THEN 1 END) as new_subs_this_month,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as new_subs_last_month,

            COALESCE(SUM(CASE WHEN created_at >= ? THEN price END), 0) as revenue_this_month,
            COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN price END), 0) as revenue_last_month,

            COUNT(DISTINCT CASE WHEN active = true AND status = 'active' AND end_date >= ? THEN user_id END) as active_academies_now,
            COUNT(DISTINCT CASE WHEN active = true AND status = 'active' AND end_date >= ? AND created_at <= ? THEN user_id END) as active_academies_prev,

            COUNT(DISTINCT CASE WHEN (status = 'expired' OR end_date < ?) THEN user_id END) as expired_academies_now,
            COUNT(DISTINCT CASE WHEN (status = 'expired' OR end_date < ?) AND created_at <= ? THEN user_id END) as expired_academies_prev
        ", [
                $startOfThisMonth,                 // new_subs_this_month
                $startOfLastMonth,
                $endOfLastMonth,                   // new_subs_last_month
                $startOfThisMonth,                 // revenue_this_month
                $startOfLastMonth,
                $endOfLastMonth,                   // revenue_last_month
                $now,                              // active_academies_now
                $startOfThisMonth,
                $endOfLastMonth,                   // active_academies_prev
                $now,                              // expired_academies_now
                $startOfThisMonth,
                $endOfLastMonth,                   // expired_academies_prev
            ])
            ->first();

        // ============ كويري خفيفة للشارت (Group By شهري) ============
        $chart = DB::table('user_packages')
            ->selectRaw($this->monthExpression() . " as month, COUNT(*) as total")
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'new_subscriptions_this_month' => [
                'value'      => (int) $stats->new_subs_this_month,
                'change_pct' => $this->percentageChange($stats->new_subs_this_month, $stats->new_subs_last_month),
            ],
            'active_academies' => [
                'value'      => (int) $stats->active_academies_now,
                'change_pct' => $this->percentageChange($stats->active_academies_now, $stats->active_academies_prev),
            ],
            'expired_academies' => [
                'value'      => (int) $stats->expired_academies_now,
                'change_pct' => $this->percentageChange($stats->expired_academies_now, $stats->expired_academies_prev),
            ],
            'total_revenue' => [
                'value'      => (float) $stats->revenue_this_month,
                'change_pct' => $this->percentageChange($stats->revenue_this_month, $stats->revenue_last_month),
            ],
            'chart_last_12_months' => $chart,
        ];
    }

    private function monthExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        return $driver === 'pgsql'
            ? "TO_CHAR(created_at, 'YYYY-MM')"
            : "DATE_FORMAT(created_at, '%Y-%m')";
    }

    private function percentageChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
