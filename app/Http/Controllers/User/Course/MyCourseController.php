<?php

namespace App\Http\Controllers\User\Course;

use App\Http\Controllers\Controller;
use App\Repositories\UserSubscribe\UserSubscribeRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MyCourseController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public UserSubscribeRepository $userRepo)
    {
    }
    public function index(Request $request)
    {
        $userId =auth('api')->id();
        $courses = $this->userRepo->mycourses($userId);
       return $this->successResponse($courses, "List Of My courses");
    }
}
