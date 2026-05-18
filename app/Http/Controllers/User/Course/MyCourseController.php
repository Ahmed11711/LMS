<?php

namespace App\Http\Controllers\User\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Course\MyCourseShowResource;
use App\Models\Course;
use App\Repositories\UserSubscribe\UserSubscribeRepository;
use App\Services\PlanAccessService\PlanAccessService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MyCourseController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        public UserSubscribeRepository $userRepo,
        public PlanAccessService $planAccessService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->get('tenant_user');

        $fromSubscribe = Course::where('status', 'published')
            ->whereHas(
                'subscribes',
                fn($q) =>
                $q->where('user_id', $user->id)
                    ->where('status', 'active')
            )->get();

        $fromPlan = $this->planAccessService->getAccessibleCourses($user);

        $allCourses = $fromSubscribe
            ->merge($fromPlan)
            ->unique('id')
            ->values();

        return $this->successResponse($allCourses, 'List Of My courses');
    }

    public function show($id, Request $request)
    {
        $user = $request->get('tenant_user');

        $course = Course::where('status', 'published')->find($id);

        if (!$course) {
            return $this->errorResponse('Course not found', 404);
        }

        // تحقق من الصلاحية — subscribe أو plan
        $hasAccess = $this->userRepo->hasCourse($user->id, $id)
            || $this->planAccessService->canAccess($user, $course);

        if (!$hasAccess) {
            return $this->errorResponse('You are not enrolled in this course', 403);
        }

        return $this->successResponse(new MyCourseShowResource($course), 'Course retrieved successfully');
    }
}
