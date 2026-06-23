<?php

use \App\Http\Controllers\Admin\Auth\SendOtpController;
use App\Http\Controllers\Admin\Auth\CheckOtpController;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\Chapter\ChapterController;
use App\Http\Controllers\Admin\Course\CourseController;
use App\Http\Controllers\Admin\CustomDomain\CustomDomainController;
use App\Http\Controllers\Admin\CustomDomain\CustomSubdomainController;
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
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Section\SectionController;
use App\Http\Controllers\Admin\Pages\PagesController;


use App\Http\Controllers\Admin\InstructorReceiverAccount\InstructorReceiverAccountController;
use App\Http\Controllers\Admin\ReceiverAccount\ReceiverAccountController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\OrganizationProfile\OrganizationProfileController;
use App\Http\Controllers\Admin\Plan\PlanController;
use App\Http\Controllers\Instructor\UserPaymentInfo\UserPaymentInfoController;
use App\Http\Controllers\Admin\UserBalance\UserBalalnceController;
use App\Http\Controllers\Admin\UserPlan\UserPlanController;
use App\Http\Controllers\Admin\UserSubscribe\UserSubscribeController;
use App\Http\Controllers\Instructor\InstructorController;
use App\Http\Controllers\Tenant\TenantMigrationController;

Route::prefix('academy')->middleware([ResolveTenant::class, TenantJwtMiddleware::class . ':admin'])->group(function () {

    Route::apiResource('pages', PagesController::class)->names('pages');
    Route::apiResource('sections', SectionController::class)->names('section')->except(['store', 'update', 'get']);
    Route::get('sections', [SectionController::class, 'byPage']);
    Route::get('sectionss', [SectionController::class, 'index']);
    Route::post('sections', [SectionController::class, 'bulkStore'])->name('section.store');


    // handel migrate new model for this tenant
    Route::post('migrate', [TenantMigrationController::class, 'migrateCurrentTenant']);

    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::apiResource('users', UserController::class)->names('user');
    Route::apiResource('categories', CategoryController::class)->names('category');
    Route::apiResource('courses', CourseController::class)
        ->except(['store'])
        ->names('course');

    Route::post('courses', [CourseController::class, 'store'])
        ->middleware(CheckFeatureLimit::class . ':max_courses')
        ->name('course.store');

    Route::apiResource('online_sessions', OnlineSessionController::class)->names('online_session');
    Route::apiResource('physical_course_details', PhysicalCourseDetailController::class)->names('physical_course_detail');
    Route::apiResource('chapters', ChapterController::class)->names('chapter');
    Route::apiResource('lessons', LessonController::class)->names('lesson');
    Route::get('me', [MeController::class, 'me'])->name('me');
    Route::post('send-otp', [SendOtpController::class, 'sendOtp'])->name('send_otp');
    Route::post('check-otp', [CheckOtpController::class, 'checkOtp'])->name('check_otp');
    Route::get('my-usage-limit', [LimitPackageController::class, 'getUsageSummary']);
    Route::get('my-package', [UserPackageController::class, 'myPacake']);
    Route::apiResource('user_subscribes', UserSubscribeController::class)->names('user_subscribe');
    /////////////////Custom Domasin ////////////////////////////////////
    Route::put('custom-domain', [CustomDomainController::class, 'setup'])->middleware(CheckFeatureLimit::class . ':custom_domain');
    Route::put('custom-subdomain', [CustomSubdomainController::class, 'setup'])->middleware(CheckFeatureLimit::class . ':custom_subdomains');
    // Route::apiResource('payment_infos', paymentInfoController::class)->names('payment_info'); // for instacjtore choose it 
    // Route::apiResource('withdraw', WithdrawController::class)->names('withdraw');
    Route::apiResource('plans', PlanController::class)->names('plan');
    Route::apiResource('user_plan', UserPlanController::class)->except('post');
    Route::get('wallet', [UserBalalnceController::class, 'index']);
    Route::apiResource('receiver_accounts', ReceiverAccountController::class)->names('receiver_account');
    Route::apiResource('instructor_receiver_accounts', InstructorReceiverAccountController::class)->names('instructor_receiver_account');

    // Route::apiResource('academy_payment_methods', AcademyPaymentMethodController::class)->names('academy_payment_method');
    // Route::apiResource('academy_withdraws', AcademyWithdrawController::class)->names('academy_withdraw');

    // for orginization profile
    Route::apiResource('organization_profiles', OrganizationProfileController::class)->names('organization_profile');
});

Route::prefix('instructor')->middleware([ResolveTenant::class, TenantJwtMiddleware::class . ':academy',])
    ->group(function () {
        Route::apiResource('courses', CourseCourseController::class)
            ->names('instructor.course');
        Route::get('wallet', [UserBalalnceController::class, 'index']);

        Route::apiResource('profile', InstructorController::class)->except('post', 'delete', 'put');
        Route::put('profile', [InstructorController::class, 'update']);
        Route::apiResource('user_payment_infos', UserPaymentInfoController::class)->names('user_payment_info');
        Route::apiResource('instructor_receiver_accounts', InstructorReceiverAccountController::class)->names('instructor_receiver_accounts');

        // Route::apiResource('user_withdraws', UserWithdrawController::class)->names('user_withdraw');
    });



Route::prefix('v1')->group(function () {});
