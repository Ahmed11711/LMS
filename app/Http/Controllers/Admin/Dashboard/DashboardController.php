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

        // ===== مبيعات الدورات لكل عملة =====
        $courseSalesByCurrency = UserSubscribe::query()
            ->join('courses', 'courses.id', '=', 'user_subscribes.course_id')
            ->where('user_subscribes.status', 'active')
            ->when(!$isAdmin, fn($q) => $q->where('courses.user_id', $user->id))
            ->selectRaw("
                COALESCE(courses.currency, 'UNKNOWN') as currency,
                COALESCE(SUM(CASE WHEN user_subscribes.price ~ '^[0-9]+(\.[0-9]+)?$' THEN CAST(user_subscribes.price AS numeric) ELSE 0 END), 0) as total,
                COALESCE(SUM(CASE WHEN user_subscribes.created_at < ? AND user_subscribes.price ~ '^[0-9]+(\.[0-9]+)?$' THEN CAST(user_subscribes.price AS numeric) ELSE 0 END), 0) as before_today
            ", [$today])
            ->groupBy('courses.currency')
            ->get();

        // ===== مبيعات الحقائب لكل عملة =====
        $bagSalesByCurrency = BagPurchase::query()
            ->join('bags', 'bags.id', '=', 'bag_purchases.bag_id')
            ->where('bag_purchases.status', 'approved')
            ->when(!$isAdmin, fn($q) => $q->where('bags.user_id', $user->id))
            ->selectRaw("
                COALESCE(bags.currency, 'UNKNOWN') as currency,
                COALESCE(SUM(bag_purchases.amount), 0) as total,
                COALESCE(SUM(CASE WHEN bag_purchases.created_at < ? THEN bag_purchases.amount ELSE 0 END), 0) as before_today
            ", [$today])
            ->groupBy('bags.currency')
            ->get();

        // ===== دمج المبيعات من المصدرين لكل عملة =====
        $salesByCurrency = [];

        foreach ($courseSalesByCurrency as $row) {
            $salesByCurrency[$row->currency]['total']        = ($salesByCurrency[$row->currency]['total'] ?? 0) + (float) $row->total;
            $salesByCurrency[$row->currency]['before_today'] = ($salesByCurrency[$row->currency]['before_today'] ?? 0) + (float) $row->before_today;
        }

        foreach ($bagSalesByCurrency as $row) {
            $salesByCurrency[$row->currency]['total']        = ($salesByCurrency[$row->currency]['total'] ?? 0) + (float) $row->total;
            $salesByCurrency[$row->currency]['before_today'] = ($salesByCurrency[$row->currency]['before_today'] ?? 0) + (float) $row->before_today;
        }

        $totalSales = [];
        foreach ($salesByCurrency as $currency => $values) {
            $totalSales[$currency] = [
                'total'      => round($values['total'], 2),
                'percentage' => $this->percentageChange($values['total'], $values['before_today']),
            ];
        }

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
            'total_sales' => $totalSales,
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
