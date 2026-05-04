<?php

use \App\Http\Controllers\Admin\Auth\SendOtpController;
use App\Http\Controllers\Admin\Auth\CheckOtpController;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\Chapter\ChapterController;
use App\Http\Controllers\Admin\Course\CourseController;
use App\Http\Controllers\Admin\CustomDomain\CustomDomainController;
use App\Http\Controllers\Admin\Lesson\LessonController;
use App\Http\Controllers\Admin\Me\MeController;
use App\Http\Controllers\Admin\OnlineSession\OnlineSessionController;
use App\Http\Controllers\Admin\PhysicalCourseDetail\PhysicalCourseDetailController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Admin\UserPackage\LimitPackageController;
use App\Http\Controllers\Admin\UserPackage\UserPackageController;
use App\Http\Controllers\Instructor\Course\CourseController as CourseCourseController;
use App\Http\Middleware\CheckFeatureLimit;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\TenantJwtMiddleware;
use App\Models\User;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserSubscribe\UserSubscribeController;
use App\Http\Controllers\Instructor\InstructorController;

//
Route::prefix('academy')->middleware([ResolveTenant::class, TenantJwtMiddleware::class])->group(function () {
    Route::apiResource('users', UserController::class)->names('user');
    Route::apiResource('categories', CategoryController::class)->names('category');
    Route::apiResource('courses', CourseController::class)->names('course')->middleware(CheckFeatureLimit::class . ':max_courses');
    Route::apiResource('online_sessions', OnlineSessionController::class)->names('online_session');
    Route::apiResource('physical_course_details', PhysicalCourseDetailController::class)->names('physical_course_detail');
    Route::apiResource('chapters', ChapterController::class)->names('chapter');
    Route::apiResource('lessons', LessonController::class)->names('lesson')->middleware(CheckFeatureLimit::class . ':storage_limit');
    Route::get('me', [MeController::class, 'me'])->name('me');
    Route::post('send-otp', [SendOtpController::class, 'sendOtp'])->name('send_otp');
    Route::post('check-otp', [CheckOtpController::class, 'checkOtp'])->name('check_otp');
    Route::get('my-usage-limit', [LimitPackageController::class, 'getUsageSummary']);
    Route::get('my-package', [UserPackageController::class, 'myPacake']);
    Route::apiResource('user_subscribes', UserSubscribeController::class)->names('user_subscribe');
    /////////////////Custom Domasin ///////////////////////////////
    Route::put('custom-domain', [CustomDomainController::class, 'setup']);

    Route::get('uu', function () {
        $cacheKey = 'tenant_settings';
        $tenant = app('tenant');
        $cacheKey = "tenant_{$tenant->id}_settings";
        return Cache::remember($cacheKey, 3600, function () {
            return User::get();
        });
    });
});

Route::prefix('instructor')->middleware([ResolveTenant::class, TenantJwtMiddleware::class . ':academy',])
    ->group(function () {
        Route::apiResource('courses', CourseCourseController::class)
            ->names('instructor.course');

        Route::apiResource('profile', InstructorController::class)->except('post', 'delete', 'put');
        Route::put('profile', [InstructorController::class, 'update']);
    });



Route::prefix('v1')->group(function () {});
Route::get('/whoami', function () {

    $domain = "darab.academy";
    $domain2 = "darrab.app";
    $domainIp = gethostbyname($domain);
    $domainIp2 = gethostbyname($domain2);

    return [
        '1' => $domainIp,
        '2' => $domainIp2,

    ];
});
