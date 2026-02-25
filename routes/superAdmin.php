<?php

use App\Http\Controllers\Admin\FeaturePackage\FeaturePackageController;
use App\Http\Controllers\Admin\Features\FeaturesController;
use App\Http\Controllers\Admin\Package\PackageController;
use App\Http\Controllers\SuperAdmin\Auth\LoginController;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Support\Facades\Route;






Route::prefix('superAdmin')->group(function () {

    Route::post('login', [LoginController::class, 'login']);


    Route::middleware(SuperAdminMiddleware::class)->group(function () {
        Route::apiResource('packages', PackageController::class)->names('package');
        Route::apiResource('features', FeaturesController::class)->names('features');
        Route::apiResource('feature_packages', FeaturePackageController::class)->names('feature_package');
    });
});
