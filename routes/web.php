<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Receptionist\ReservationController;
use App\Http\Controllers\Receptionist\ClientController;
use App\Http\Controllers\StripeController;



// this is an example on how to authorize based on permissions // our practice when it comes to Authorization : permisson gets assigned to roles, roles gets assigned to users then in middleware check permission names, or sometimes role-names
// to add/remove/edit permissions or roles modify the ./database/seeders/RolesAndPermissionsSeeder
// more can be found @ https://spatie.be/docs/laravel-permission/v6/best-practices/roles-vs-permissions
Route::get('/floors', function () {
    return Inertia::render('Welcome');
})->middleware(['auth','permission:manage floors'])->name('floors.index');


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'permission:manage clients'])
    ->prefix('receptionist')
    ->name('receptionist.')
    ->group(function () {
        // Client management routes
        Route::get('clients/pending', [ClientController::class, 'pendingClients'])->name('clients.pending');
        Route::get('clients/approved', [ClientController::class, 'approvedClients'])->name('clients.approved');
        Route::post('clients/{id}/approve', [ClientController::class, 'approveClient'])->name('clients.approve');
        Route::get('clients/reservations', [ClientController::class, 'clientReservations'])->name('clients.reservations');
        Route::get('clients/{id}/reservations', [ClientController::class, 'clientReservations'])->name('clients.client-reservations');
    });

Route::middleware(['auth', 'permission:manage reservations'])
    ->prefix('receptionist')
    ->name('receptionist.')
    ->group(function () {
        // Use resource routing for ReservationController
        Route::resource('reservations', ReservationController::class);
    });
    // Generated Routes:
    // GET /receptionist/reservations → receptionist.reservations.index
    // GET /receptionist/reservations/create → receptionist.reservations.create
    // POST /receptionist/reservations → receptionist.reservations.store
    // GET /receptionist/reservations/{reservation} → receptionist.reservations.show
    // GET /receptionist/reservations/{reservation}/edit → receptionist.reservations.edit
    // PUT/PATCH /receptionist/reservations/{reservation} → receptionist.reservations.update
    // DELETE /receptionist/reservations/{reservation} → receptionist.reservations.destroy
    



Route::middleware(['auth','permission:pay for reservations'])->group(function () {
    Route::get('/reservations/checkout/{reservation_id}', [StripeController::class, 'checkout'])->name('stripe.checkout');
    Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
    Route::get('/stripe/cancel/{reservation_id?}', [StripeController::class, 'cancel'])->name('stripe.cancel');
});

// no web or csrf middleware to accept post requests from stripe with no problems, we can later move this route to api.php file and adjust success url in stripe controller if needed
Route::withoutMiddleware(['web', 'csrf'])->post('/webhook/stripe', [StripeController::class, 'handleWebhook'])->name('stripe.webhook');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
