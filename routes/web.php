<?php

use App\Http\Controllers\Client\ClientController as ClientUserController;
use App\Http\Controllers\Client\ReservationController as ClientReservationController;
use App\Http\Controllers\Receptionist\ClientController;
use App\Http\Controllers\Receptionist\ReservationController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// this is an example on how to authorize based on permissions // our practice when it comes to Authorization : permisson gets assigned to roles, roles gets assigned to users then in middleware check permission names, or sometimes role-names
// to add/remove/edit permissions or roles modify the ./database/seeders/RolesAndPermissionsSeeder
// more can be found @ https://spatie.be/docs/laravel-permission/v6/best-practices/roles-vs-permissions
Route::get('/floors', function () {
    return Inertia::render('Welcome');
})->middleware(['auth', 'permission:manage floors'])->name('floors.index');

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Receptionist Routes
Route::middleware(['auth', 'permission:manage clients'])
    ->prefix('receptionist')
    ->name('receptionist.')
    ->group(function () {
        // Client management routes
        Route::get('clients', [ClientController::class, 'approvedClients'])->name('clients.index');
        Route::get('clients/approved', [ClientController::class, 'approvedClients'])->name('clients.approved');
        Route::get('clients/my-approved', [ClientController::class, 'myApprovedClients'])->name('clients.my-approved');
        Route::post('clients/{id}/approve', [ClientController::class, 'approveClient'])->name('clients.approve');
        Route::post('clients/{id}/reject', [ClientController::class, 'rejectClient'])->name('clients.reject');
        Route::post('clients/{id}/ban', [ClientController::class, 'banClient'])->name('clients.ban');
        Route::post('clients/{id}/unban', [ClientController::class, 'unbanClient'])->name('clients.unban');
        Route::get('clients/reservations', [ClientController::class, 'clientReservations'])->name('clients.reservations');
        Route::get('clients/{id}/reservations', [ClientController::class, 'clientReservations'])->name('clients.client-reservations');
    });

Route::middleware(['auth', 'permission:manage reservations'])
    ->prefix('receptionist')
    ->name('receptionist.')
    ->group(function () {
        // Use resource routing for ReservationController
        Route::resource('reservations', ReservationController::class);
        // Add route for all reservations
        Route::get('all-reservations', [ReservationController::class, 'allReservations'])->name('reservations.all');
    });

// Client Routes
Route::middleware(['auth', 'role:client', 'client.approved'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        // Client dashboard
        Route::get('dashboard', [ClientUserController::class, 'dashboard'])->name('dashboard');

        // Client profile
        Route::get('profile', [ClientUserController::class, 'profile'])->name('profile');
        Route::put('profile', [ClientUserController::class, 'updateProfile'])->name('profile.update');

        // Client reservations
        Route::get('reservations', [ClientReservationController::class, 'index'])->name('reservations.index');
        Route::get('reservations/{reservation}', [ClientReservationController::class, 'show'])->name('reservations.show');
        Route::post('reservations/{reservation}/cancel', [ClientReservationController::class, 'cancel'])->name('reservations.cancel');

        // Make new reservations
        Route::get('available-rooms', [ClientReservationController::class, 'availableRooms'])->name('available-rooms');
        Route::get('reservations/rooms/{roomId}/create', [ClientReservationController::class, 'create'])->name('reservations.create');
        Route::post('reservations/rooms/{roomId}', [ClientReservationController::class, 'store'])->name('reservations.store');
    });

// Stripe Payment Routes
Route::middleware(['auth', 'permission:pay for reservations'])->group(function () {
    Route::get('/reservations/checkout/{reservation_id}', [StripeController::class, 'checkout'])->name('stripe.checkout');
    Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
    Route::get('/stripe/cancel/{reservation_id?}', [StripeController::class, 'cancel'])->name('stripe.cancel');
});

// Stripe Webhook (no web or csrf middleware to accept post requests from stripe)
Route::withoutMiddleware(['web', 'csrf'])->post('/webhook/stripe', [StripeController::class, 'handleWebhook'])->name('stripe.webhook');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
