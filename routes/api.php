<?php

use App\Http\Controllers\Admin\LandingPage\LandingPageController;
use App\Http\Controllers\Admin\Pages\PagesController;
use App\Http\Controllers\Admin\Plan\PlanController;
use App\Http\Controllers\Auth\ForgetRestPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Center\Auth\CreateAccountAcademyController;
use App\Http\Controllers\Center\Auth\LoginAccountController;
use App\Http\Controllers\Center\Payment\KashierPaymentController;
use App\Http\Controllers\Front\Package\PackageController;
use App\Http\Controllers\Tenant\CreateTenantController;
use App\Http\Controllers\User\Course\CourseController;
use App\Http\Controllers\User\Course\MyCourseController;
use App\Http\Controllers\User\Lesson\LessonCommentController;
use App\Http\Controllers\User\Lesson\LessonNoteController;
use App\Http\Controllers\User\Lesson\LessonProgressController;
use App\Http\Controllers\User\Profile\ProfileController;
use App\Http\Controllers\User\UserPlan\UserPlanController;
use App\Http\Controllers\User\UserSubscribe\UserSubscribeController;
use App\Http\Middleware\EnsureEnrolled;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\TenantJwtMiddleware;
use App\Models\Central\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;




// push ahmed



Route::middleware([ResolveTenant::class])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [LoginController::class, 'login']);
        Route::post('forget-password', [ForgetRestPasswordController::class, 'forgetPassword']);
        Route::post('verify-otp', [ForgetRestPasswordController::class, 'verifyOtp']);
        Route::post('reset-password', [ForgetRestPasswordController::class, 'resetPassword']);
    });
});


Route::post('create-tenant', [CreateTenantController::class, 'store']);
// ////////////////////////////////// create Account Academy /////////////////////////////
Route::prefix('front')->group(function () {
    Route::post('create-account-academy', [CreateAccountAcademyController::class, 'create']);
    Route::post('create-account-info-academy', [CreateAccountAcademyController::class, 'createInfoAcademy']);
    Route::post('create-link-payment', [KashierPaymentController::class, 'createLink']);
    Route::post('login-account-academy', [LoginAccountController::class, 'login']);
    Route::get('packages', [PackageController::class, 'activePackage']);
    Route::get('tenant', function () {
        return User::with('tenant')->get();
    });
});




Route::prefix('user')->middleware([ResolveTenant::class])->group(function () {

    Route::get('pages', [PagesController::class, 'index']);
    Route::get('landing_pages', [LandingPageController::class, 'index']);

    Route::get('profile', [ProfileController::class, 'show'])->middleware(TenantJwtMiddleware::class . ':student');
    Route::post('profile', [ProfileController::class, 'update'])->middleware(TenantJwtMiddleware::class . ':student');
    Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{slug}', [CourseController::class, 'show']);
    Route::post('user-subscribe', [UserSubscribeController::class, 'store'])->middleware(TenantJwtMiddleware::class . ':student');
    Route::get('user-subscribe', [UserSubscribeController::class, 'index'])->middleware(TenantJwtMiddleware::class . ':student');

    Route::get('my-courses', [MyCourseController::class, 'index'])->middleware(TenantJwtMiddleware::class . ':student');
    Route::get('my-courses/{id}', [MyCourseController::class, 'show'])->middleware([TenantJwtMiddleware::class . ':student']);
    Route::get('my-courses/landingpage/{slug}', [MyCourseController::class, 'landingpageMyCourse'])->middleware(TenantJwtMiddleware::class . ':student');

    Route::prefix('auth')->group(function () {
        Route::post('login', [LoginController::class, 'login']);
        Route::post('register', [LoginController::class, 'register']);
    });

    Route::get('plans', [PlanController::class, 'index']);
    Route::post('subscribe-plan', [UserPlanController::class, 'store'])->middleware(TenantJwtMiddleware::class . ':student');
    Route::prefix('lessons/{lessonId}')->middleware([TenantJwtMiddleware::class . ':student', EnsureEnrolled::class,])
        ->group(function () {

            Route::get('comments', [LessonCommentController::class, 'index']);
            Route::post('comments', [LessonCommentController::class, 'store']);
            Route::delete('comments/{commentId}', [LessonCommentController::class, 'destroy']);
            Route::post('comments/{commentId}/like', [LessonCommentController::class, 'toggleLike']);

            // Notes
            Route::get('notes', [LessonNoteController::class, 'index']);
            Route::post('notes', [LessonNoteController::class, 'store']);
            Route::put('notes/{noteId}', [LessonNoteController::class, 'update']);
            Route::delete('notes/{noteId}', [LessonNoteController::class, 'destroy']);

            // Progress
            Route::post('progress', [LessonProgressController::class, 'update']);
        });
});







Route::match(['GET', 'POST'], '/meta-webhook', function (Request $request) {

    $verifyToken = 'my_secret_token_123';

    Log::info('Meta Webhook Request', [
        'method'  => $request->method(),
        'url'     => $request->fullUrl(),
        'query'   => $request->query(),
        'body'    => $request->all(),
        'headers' => $request->headers->all(),
        'raw'     => $request->getContent(),
    ]);

    if ($request->isMethod('GET')) {

        $mode = $request->input('hub.mode');
        $token = $request->input('hub.verify_token');
        $challenge = $request->input('hub.challenge');

        Log::info('Meta Verify Request', [
            'mode' => $mode,
            'token' => $token,
            'challenge' => $challenge,
            'expected_token' => $verifyToken,
        ]);

        if ($mode === 'subscribe' && $token === $verifyToken) {

            Log::info('Meta Verify Success');

            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('Meta Verify Failed');

        return response()->json([
            'status' => 'forbidden',
            'mode' => $mode,
            'token' => $token,
            'expected' => $verifyToken,
        ], 403);
    }

    Log::info('Meta Event Received', [
        'payload' => $request->all(),
        'raw' => $request->getContent(),
    ]);

    return response()->json([
        'status' => 'received',
    ], 200);
});















require __DIR__ . '/admin.php';
require __DIR__ . '/superAdmin.php';
