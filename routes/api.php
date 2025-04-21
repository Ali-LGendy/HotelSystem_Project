<?php

use App\Http\Controllers\Receptionist\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});
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
