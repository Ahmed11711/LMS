<?php

namespace App\Http\Middleware;

use App\Models\Lesson;
use App\Repositories\UserSubscribe\UserSubscribeRepository;
use App\Services\PlanAccessService\PlanAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsureEnrolled
{
    public function __construct(
        private UserSubscribeRepository $userRepo,
        private PlanAccessService $planAccessService,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user     = $request->get('tenant_user');
        $lessonId = $request->route('lessonId');
        Log::info('EnsureEnrolled Middleware: User ID: ' . $user->id . ', Lesson ID: ' . $lessonId);

        $lesson = Lesson::with('chapter.course')->find($lessonId);

        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 200);
        }

        $course   = $lesson->chapter->course;
        $courseId = $course->id;

        $hasAccess = $this->userRepo->hasCourse($user->id, $courseId)
            || $this->planAccessService->canAccess($user, $course);

        if (!$hasAccess) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        $request->attributes->set('course', $course);
        $request->attributes->set('lesson', $lesson);

        return $next($request);
    }
}
