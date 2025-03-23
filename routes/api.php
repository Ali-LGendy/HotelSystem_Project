<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Receptionist\ReservationController;

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

// API routes for clients
Route::middleware(['auth:sanctum'])
    ->prefix('api')
    ->name('api.')
    ->group(function () {
        Route::get('clients/approved', [App\Http\Controllers\Api\ClientController::class, 'approvedClients'])->name('clients.approved');
    });