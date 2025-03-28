<?php

use App\Http\Controllers\Receptionist\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'permission:manage reservations'])
    ->prefix('receptionist')
    ->name('receptionist.')
    ->group(function () {
        Route::get('reservations', [ReservationController::class, 'index']);
        Route::get('reservations/{reservation}', [ReservationController::class, 'show']);
        Route::post('reservations', [ReservationController::class, 'store']);
        Route::put('reservations/{reservation}', [ReservationController::class, 'update']);
        Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy']);
    });

// API routes for the application

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
