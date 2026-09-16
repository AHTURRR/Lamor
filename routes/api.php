<?php

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Middleware\ValidateTrackingToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Admin-authenticated routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Session management
    Route::apiResource('sessions', SessionController::class)->except(['update']);

    // Location data for admin dashboard
    Route::get('/locations/all-latest', [LocationController::class, 'allLatest']);
    Route::get('/locations/{sessionId}/history', [LocationController::class, 'history']);
    Route::get('/locations/{sessionId}/latest', [LocationController::class, 'latest']);
});

// Target client route (token-authenticated via middleware)
Route::post('/locations', [LocationController::class, 'store'])
    ->middleware([ValidateTrackingToken::class, 'throttle:20,1']);
