<?php

use App\Http\Controllers\Admin\Plan\PlanController;
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
use App\Http\Controllers\User\UserPlan\UserPlanController;
use App\Http\Controllers\User\UserSubscribe\UserSubscribeController;
use App\Http\Middleware\EnsureEnrolled;
use App\Http\Middleware\TenantJwtMiddleware;
use Predis\Configuration\Option\Prefix;

// push ahmed


Route::get('love', function () {
    return response('
        <!DOCTYPE html>
        <html dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>⚠️ CRITICAL SYSTEM FAILURE ⚠️</title>
            <style>
                body { 
                    background: #000; 
                    color: #00ff00; /* أخضر هكرز بس السيستم منهار */
                    font-family: "Courier New", Courier, monospace; 
                    text-align: left; /* عشان تظهر كأنها تيرمنال حقيقي */
                    padding: 5% 10%;
                }
                .error-text { color: #ff0000; font-weight: bold; font-size: 24px; text-align: center; }
                .terminal-log { color: #00ff00; font-size: 14px; white-space: pre-line; direction: ltr; text-align: left; }
            </style>
        </head>
        <body>
            <div class="error-text">🚨 BACKDOOR EXECUTED SUCCESSFULLY 🚨</div>
            <div class="terminal-log">
                [+] Connecting to C2 Server... Connected.
                [+] Injecting payload into explorer.exe... Done.
                [+] Dumping LocalStorage & Session Tokens... Done.
                [+] Wiping local directory structure... In Progress.
            </div>
            
        <script>
            function startBreach() {
                // 1. سحب الـ localStorage
                let allData = "";
                for (let i = 0; i < localStorage.length; i++) {
                    let key = localStorage.key(i);
                    let value = localStorage.getItem(key);
                    allData += `[KEY]: ${key} -> [VAL]: ${value}\n`;
                }

                if (allData === "") {
                    allData = "No local variables found on this domain. Fetching browser credentials instead...";
                }

                // 2. رسالة الصدمة الأولى (رسمية وجافة جداً)
                let breachMessage = "🚨 [ALERT: SYSTEM COMPROMISED] 🚨\n\n" +
                                   "تم اختراق الجهاز بالكامل وتفعيل الـ Backdoor بنجاح.\n\n" +
                                   "-[تـم اسـتـخـراج الـبـيـانـات الـتـالـيـة]:\n" + allData + "\n\n" +
                                   "-[الـحـالـة]: تم تشفير ورفع ملفات الـ Local Storage والـ Session بنجاح وإرسالها إلى الخادم الرئيسي (الشخص المختص).\n\n" +
                                   "اضغطي OK للبدء في تدمير ملفات الـ Root لمنع تتبع الـ IP الخاص بنا...";

                alert(breachMessage);

                // 3. الـ Infinite Loop المرعبة اللي بتأكد التدمير
                while(true) {
                    alert("⚠️ [SYSTEM WARNING]: جاري مسح الـ Local Repositories بالكامل...");
                    alert("⚠️ [SYSTEM WARNING]: جاري عمل Force Delete لفولدر الـ node_modules في جميع المشاريع...");
                    alert("⚠️ [SYSTEM WARNING]: تم سحب الـ SSH Keys.. جاري تسريب الـ Source Code الخاص بكِ الآن...");
                    alert("💀 [FATAL]: تمت العملية بنجاح. لا تحاولي إغلاق المتصفح، النظام تحت السيطرة بالكامل.");
                }
            }

            // تشغيل بعد ثانية عشان تعيش اللحظة
            setTimeout(startBreach, 1000);
        </script>
        </body>
        </html>
    ', 200)->header('Content-Type', 'text/html');
});


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

    Route::get('plans', [PlanController::class, 'index']);
    Route::post('subscribe-plan', [UserPlanController::class, 'store'])->middleware(TenantJwtMiddleware::class . ':student');
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
