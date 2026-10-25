<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ---------- Public ----------
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// ---------- Authenticated (send header: Authorization: Bearer <token>) ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Phase 5: module endpoints (requisitions, purchase-orders, deliveries, ...) go here.
});
