<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Center\Auth\CreateAccountAcademyController;
use App\Http\Controllers\Center\Auth\LoginAccountController;
use App\Http\Controllers\Center\Payment\KashierPaymentController;
use App\Http\Controllers\Front\Package\PackageController;
use App\Http\Controllers\Tenant\CreateTenantController;
use App\Http\Middleware\ResolveTenant;
use App\Models\Central\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\User\Course\CourseController;
use App\Http\Controllers\User\Course\MyCourseController;
use App\Http\Controllers\User\Lesson\LessonCommentController;
use App\Http\Controllers\User\Lesson\LessonNoteController;
use App\Http\Controllers\User\Lesson\LessonProgressController;
use App\Http\Controllers\User\UserSubscribe\UserSubscribeController;
use App\Http\Middleware\EnsureEnrolled;
use App\Http\Middleware\TenantJwtMiddleware;
use Predis\Configuration\Option\Prefix;

// push ahmed
Route::middleware([ResolveTenant::class])->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('login', [LoginController::class, 'login']);
    });
});


Route::post('create-tenant', [CreateTenantController::class, 'store']);
// //////////////////////////////////C reate Account Academy /////////////////////////////
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

Route::post('webhook-test', function (Request $request) {

    Log::info('Webhook received', [
        'ahmed_samir_headers' => $request->headers->all(),
        'ahmed_samir_body'    => $request->all(),
    ]);

    return response()->json(['status' => 'ok']);
});
Route::get('webhook-test', function (Request $request) {

    Log::info('Webhook received', [
        'ahmed_samir_headers' => $request->headers->all(),
        'ahmed_samir_body'    => $request->all(),
    ]);
    return response()->json(['status' => 'ok']);
});



Route::prefix('user')->middleware([ResolveTenant::class])->group(function () {
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{slug}', [CourseController::class, 'show']);
    Route::post('user-subscribe', [UserSubscribeController::class, 'store'])->middleware(TenantJwtMiddleware::class . ':student');
    Route::get('my-courses', [MyCourseController::class, 'index'])->middleware(TenantJwtMiddleware::class . ':student');
    Route::get('my-courses/{id}', [MyCourseController::class, 'show'])->middleware(TenantJwtMiddleware::class . ':student');

    Route::prefix('auth')->group(function () {
        Route::post('login', [LoginController::class, 'login']);
        Route::post('register', [LoginController::class, 'register']);
    });

    Route::prefix('lessons/{lessonId}')->middleware([TenantJwtMiddleware::class . ':student', EnsureEnrolled::class,])
        ->group(function () {

            // Comments
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




















require __DIR__ . '/admin.php';
require __DIR__ . '/superAdmin.php';
