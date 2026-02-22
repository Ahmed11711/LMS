<?php

use App\Http\Controllers\Auth\CreateAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Tenant\CreateTenantController;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Support\Facades\Route;

Route::middleware([ResolveTenant::class])->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('create-account', [CreateAccountController::class, 'create']);
        Route::post('login', [LoginController::class, 'login']);
    });
});

Route::post('create-tenant', [CreateTenantController::class, 'store']);

require __DIR__ . '/admin.php';
