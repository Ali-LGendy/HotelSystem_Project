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

// API routes for clients
Route::middleware(['auth:sanctum'])
    ->prefix('api')
    ->name('api.')
    ->group(function () {
        Route::get('clients/approved', [App\Http\Controllers\Api\ClientController::class, 'approvedClients'])->name('clients.approved');
    });

// Direct API route for client approval
Route::middleware(['auth:sanctum', 'permission:manage clients'])
    ->post('/api/receptionist/clients/{id}/approve', [App\Http\Controllers\Receptionist\ClientController::class, 'approveClientApi'])
    ->name('api.receptionist.clients.approve');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
