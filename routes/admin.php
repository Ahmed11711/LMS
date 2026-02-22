<?php

use App\Http\Controllers\Admin\User\UserController;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\TenantJwtMiddleware;
use Illuminate\Support\Facades\Route;


Route::middleware([ResolveTenant::class, TenantJwtMiddleware::class])->group(function () {
    Route::apiResource('users', UserController::class)->names('user');
});
