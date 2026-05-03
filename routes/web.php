<?php

use App\Http\Controllers\Center\Payment\KashierPaymentController;
use App\Http\Controllers\Payment\WebHookSubscribeController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/kashier/create', [KashierPaymentController::class, 'create']);
Route::get('/kashier/success', [KashierPaymentController::class, 'success'])->name('kashier.success');
Route::get('/kashier/failure', [KashierPaymentController::class, 'failure'])->name('kashier.failure');
Route::post('/kashier/webhook', [KashierPaymentController::class, 'handle'])->name('kashier.webhook');


Route::get('kashier/webhook/ahmed/{id}', [WebHookSubscribeController::class, 'handle']);
Route::post('kashier/webhook/ahmed/{id}', [WebHookSubscribeController::class, 'handle']);
// Route::post('bunny/webhook', function (\Illuminate\Http\Request $request) {
//     \Illuminate\Support\Facades\Log::info('Bunny webhook', [
//         'headers' => $request->headers->all(),
//         'body'    => $request->all(),
//         'raw'     => $request->getContent(),
//     ]);

//     return response()->json(['message' => 'ok']);
// });
Route::get('bunny/webhook', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('Bunny webhook', [
        'headers' => $request->headers->all(),
        'body'    => $request->all(),
        'raw'     => $request->getContent(),
    ]);

    return response()->json(['message' => 'ok']);
});



Route::post('bunny/webhook', function (\Illuminate\Http\Request $request) {
    Log::info('Bunny webhook: received', [
        'body' => $request->all(),
    ]);

    $videoId   = $request->input('VideoGuid');
    $libraryId = $request->input('VideoLibraryId');
    $status    = $request->input('Status');

    Log::info('Bunny webhook: parsed', [
        'video_id'   => $videoId,
        'library_id' => $libraryId,
        'status'     => $status,
    ]);

    return response()->json([
        'videoId'    => $videoId,
        'libraryId'  => $libraryId,
        'signature'  => 'TODO',
        'expirationTime' => 'TODO',
    ]);
});
Route::get('/test2', function () {
    return 6 * 7;
});
