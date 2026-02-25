<?php

use App\Http\Controllers\Auth\CreateAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Center\Auth\CreateAccountAcademyController;
use App\Http\Controllers\Center\Auth\LoginAccountController;
use App\Http\Controllers\Center\Payment\KashierPaymentController;
use App\Http\Controllers\Front\Package\PackageController;
use App\Http\Controllers\Tenant\CreateTenantController;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Support\Facades\Route;




Route::middleware([ResolveTenant::class])->group(function () {

    // student and academy login and register
    // Route::prefix('auth')->group(function () {
    //     Route::post('create-account', [CreateAccountController::class, 'create']);
    //     Route::post('login', [LoginController::class, 'login']);
    // });
});

Route::post('create-account', [CreateAccountController::class, 'create']);
Route::post('create-tenant', [CreateTenantController::class, 'store']);

// //////////////////////////////////Create Account Academy /////////////////////////////
Route::prefix('front')->group(function () {
    Route::post('create-account-academy', [CreateAccountAcademyController::class, 'create']);
    Route::post('login-account-academy', [LoginAccountController::class, 'login']);
    Route::post('create-link-payment', [KashierPaymentController::class, 'createLink']);
    Route::get('packages', [PackageController::class, 'activePackage']);
});


require __DIR__ . '/admin.php';
require __DIR__ . '/superAdmin.php';
