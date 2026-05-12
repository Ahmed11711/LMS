<?php

namespace App\Http\Middleware;

use App\Models\Lesson;
use App\Repositories\UserSubscribe\UserSubscribeRepository;
use Closure;
use Illuminate\Http\Request;

class EnsureEnrolled
{
    public function __construct(private UserSubscribeRepository $userRepo) {}

    public function handle(Request $request, Closure $next)
    {
        $user     = $request->get('tenant_user');
        $lessonId = $request->route('lessonId');

        $lesson = Lesson::find($lessonId);

        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }

        if (!$this->userRepo->hasCourse($user->id, $lesson->chapter->course_id)) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        return $next($request);
    }
}
