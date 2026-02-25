<?php

use App\Http\Controllers\Admin\User\UserController;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\TenantJwtMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FeaturePackage\FeaturePackageController;
use App\Http\Controllers\Admin\Features\FeaturesController;
use App\Http\Controllers\Admin\Package\PackageController;


Route::middleware([ResolveTenant::class, TenantJwtMiddleware::class])->group(function () {
    Route::apiResource('users', UserController::class)->names('user');
});


// 

Route::prefix('v1')->group(function () {});
