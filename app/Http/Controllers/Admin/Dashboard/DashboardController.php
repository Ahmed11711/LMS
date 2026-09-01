<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Bag;
use App\Models\BagPurchase;
use App\Models\User;
use App\Models\UserSubscribe;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $isAdmin = in_array($user->role, ['admin', 'super_admin']);

        $cacheKey = $isAdmin ? 'dashboard_stats_admin' : "dashboard_stats_user_{$user->id}";

        $data = Cache::remember($cacheKey, 30, function () use ($user, $isAdmin) {
            return $this->buildDashboardData($user, $isAdmin);
        });

        return response()->json($data);
    }

    private function buildDashboardData($user, bool $isAdmin): array
    {
        $today = Carbon::today();

        $coursesStats = Course::query()
            ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
            ->selectRaw('
                COUNT(*) as total,
                COUNT(*) FILTER (WHERE created_at < ?) as before_today
            ', [$today])
            ->first();

        // ===== الحقائب =====
        $bagsStats = Bag::query()
            ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
            ->selectRaw('
                COUNT(*) as total,
                COUNT(*) FILTER (WHERE created_at < ?) as before_today
            ', [$today])
            ->first();

        // ===== الطلاب الجدد =====
        $studentsBaseQuery = User::query()->where('role', 'student');
        if (!$isAdmin) {
            $studentsBaseQuery->whereHas('subscribes.course', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $studentsStats = (clone $studentsBaseQuery)
            ->selectRaw('
                COUNT(*) as total,
                COUNT(*) FILTER (WHERE users.created_at < ?) as before_today
            ', [$today])
            ->first();

        // ===== مبيعات الدورات =====
        $courseSalesStats = UserSubscribe::query()
            ->where('status', 'active')
            ->when(!$isAdmin, fn($q) => $q->whereHas('course', fn($qq) => $qq->where('user_id', $user->id)))
            ->selectRaw("
                COALESCE(SUM(CASE WHEN price ~ '^[0-9]+(\.[0-9]+)?$' THEN CAST(price AS numeric) ELSE 0 END), 0) as total,
                COALESCE(SUM(CASE WHEN created_at < ? AND price ~ '^[0-9]+(\.[0-9]+)?$' THEN CAST(price AS numeric) ELSE 0 END), 0) as before_today
            ", [$today])
            ->first();

        // ===== مبيعات الحقائب =====
        $bagSalesStats = BagPurchase::query()
            ->where('status', 'approved')
            ->when(!$isAdmin, fn($q) => $q->whereHas('bag', fn($qq) => $qq->where('user_id', $user->id)))
            ->selectRaw('
                COALESCE(SUM(amount), 0) as total,
                COALESCE(SUM(CASE WHEN created_at < ? THEN amount ELSE 0 END), 0) as before_today
            ', [$today])
            ->first();

        $salesTotal       = $courseSalesStats->total + $bagSalesStats->total;
        $salesBeforeToday = $courseSalesStats->before_today + $bagSalesStats->before_today;

        // ===== آخر 3 دورات =====
        $latestCourses = Course::query()
            ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
            ->select(['id', 'title', 'image', 'price', 'final_price', 'status', 'created_at'])
            ->latest('id')
            ->limit(3)
            ->get();

        // ===== آخر 3 مستخدمين (طلاب) =====
        $latestUsersQuery = User::query()->where('role', 'student');
        if (!$isAdmin) {
            $latestUsersQuery->whereHas('subscribes.course', fn($q) => $q->where('user_id', $user->id));
        }
        $latestUsers = $latestUsersQuery
            ->select(['id', 'name', 'email', 'profile_image', 'created_at'])
            ->latest('id')
            ->limit(3)
            ->get();

        return [
            'courses' => [
                'total'      => (int) $coursesStats->total,
                'percentage' => $this->percentageChange($coursesStats->total, $coursesStats->before_today),
            ],
            'bags' => [
                'total'      => (int) $bagsStats->total,
                'percentage' => $this->percentageChange($bagsStats->total, $bagsStats->before_today),
            ],
            'new_students' => [
                'total'      => (int) $studentsStats->total,
                'percentage' => $this->percentageChange($studentsStats->total, $studentsStats->before_today),
            ],
            'total_sales' => [
                'total'      => round($salesTotal, 2),
                'percentage' => $this->percentageChange($salesTotal, $salesBeforeToday),
            ],
            'latest_courses' => $latestCourses,
            'latest_users'   => $latestUsers,
        ];
    }

    private function percentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
