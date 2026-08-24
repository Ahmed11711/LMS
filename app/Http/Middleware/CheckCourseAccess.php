<?php

namespace App\Http\Middleware;

use App\Models\UserSubscribe;
use Closure;
use Illuminate\Http\Request;

class CheckCourseAccess
{
    public function handle(Request $request, Closure $next)
    {
        $userId   = $request->attributes->get('user_id');
        $courseId = $request->route('id');

        $subscription = UserSubscribe::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'أنت غير مشترك في هذا الكورس',
            ], 403);
        }

        if (
            $subscription->status === 'active' &&
            $subscription->ends_at !== null &&
            $subscription->ends_at->isPast()
        ) {
            $subscription->update(['status' => 'completed']);
        }

        if ($subscription->status !== 'active') {
            $message = $subscription->status === 'completed'
                ? 'لقد أكملت هذا الكورس'
                : 'لا يمكنك الوصول، اشتراكك غير مفعل';

            return response()->json([
                'message' => $message,
            ], 403);
        }

        return $next($request);
    }
}
