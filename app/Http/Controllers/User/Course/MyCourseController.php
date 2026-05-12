<?php

namespace App\Http\Controllers\User\Course;

use App\Http\Controllers\Controller;
use App\Repositories\UserSubscribe\UserSubscribeRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MyCourseController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public UserSubscribeRepository $userRepo) {}
    public function index(Request $request)
    {
        $user =  $request->get('tenant_user');
        $courses = $this->userRepo->mycourses($user->id);
        return $this->successResponse($courses, "List Of My courses");
    }

    public function show($id, Request $request)
    {
        $user = $request->get('tenant_user');

        if (!$this->userRepo->hasCourse($user->id, $id)) {
            return $this->errorResponse('You are not subscribed to this course', 403);
        }

        $course = $this->userRepo->getCourseById($user->id, $id);

        return $this->successResponse($course, 'Course retrieved successfully');
    }
}
