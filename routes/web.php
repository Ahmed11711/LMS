<?php

use App\Http\Controllers\Center\Payment\KashierPaymentController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});
// Route::get('/kashier/create', [KashierPaymentController::class, 'create']);
Route::get('/kashier/success', [KashierPaymentController::class, 'success'])->name('kashier.success');
Route::get('/kashier/failure', [KashierPaymentController::class, 'failure'])->name('kashier.failure');
Route::post('/kashier/webhook', [KashierPaymentController::class, 'handle'])->name('kashier.webhook');


Route::get('/test2', function () {
    return 6 * 7;
});
