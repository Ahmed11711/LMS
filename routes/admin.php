<?php

use App\Http\Controllers\Admin\User\UserController;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\TenantJwtMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Lesson\LessonController;
use App\Http\Controllers\Admin\Chapter\ChapterController;
use App\Http\Controllers\Admin\PhysicalCourseDetail\PhysicalCourseDetailController;
use App\Http\Controllers\Admin\OnlineSession\OnlineSessionController;
use App\Http\Controllers\Admin\Course\CourseController;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\Me\MeController;

Route::prefix('academy')->middleware([ResolveTenant::class, TenantJwtMiddleware::class])->group(function () {
    Route::apiResource('users', UserController::class)->names('user');
    Route::apiResource('categories', CategoryController::class)->names('category');
    Route::apiResource('courses', CourseController::class)->names('course');
    Route::apiResource('online_sessions', OnlineSessionController::class)->names('online_session');
    Route::apiResource('physical_course_details', PhysicalCourseDetailController::class)->names('physical_course_detail');
    Route::apiResource('chapters', ChapterController::class)->names('chapter');
    Route::apiResource('lessons', LessonController::class)->names('lesson');
    Route::get('me', [MeController::class, 'me'])->name('me');
});




Route::prefix('v1')->group(function () {});
