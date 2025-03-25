<?php

// use App\Http\Controllers\Receptionist\ClientController;
use App\Http\Controllers\EssamClientController;
use App\Http\Controllers\Receptionist\ReservationController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ManageClientsController;
use App\Http\Controllers\ManagersController;
use App\Http\Controllers\ManageReceptionistsController;

// this is an example on how to authorize based on permissions // our practice when it comes to Authorization : permisson gets assigned to roles, roles gets assigned to users then in middleware check permission names, or sometimes role-names
// to add/remove/edit permissions or roles modify the ./database/seeders/RolesAndPermissionsSeeder
// more can be found @ https://spatie.be/docs/laravel-permission/v6/best-practices/roles-vs-permissions
Route::get('/floors', function () {
    return Inertia::render('Welcome');
})->middleware(['auth', 'permission:manage floors'])->name('floors.index');

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

    
Route::middleware(['auth'])->get('/dashboardd', [DashboardController::class, 'index'])->name('dashboard.index');



Route::middleware(['auth','permission:manage managers'])->prefix('admin/users/managers')->name('admin.users.managers.')->group(function () {
    Route::get('/', [ManagersController::class, 'index'])->name('index');        // List all managers
    Route::get('/create', [ManagersController::class, 'create'])->name('create'); // Show create form
    Route::post('/', [ManagersController::class, 'store'])->name('store');        // Store a new manager
    Route::get('/{user}', [ManagersController::class, 'show'])->name('show');     // Show specific manager
    Route::get('/{user}/edit', [ManagersController::class, 'edit'])->name('edit'); // Edit manager form
    Route::put('/{user}', [ManagersController::class, 'update'])->name('update');  // Update manager
    Route::delete('/{user}', [ManagersController::class, 'destroy'])->name('destroy'); // Delete manager
});

Route::middleware(['auth', 'permission:manage receptionists'])->prefix('admin/users/receptionists')->name('admin.users.receptionists.')->group(function () {
    Route::get('/', [ReceptionistsController::class, 'index'])->name('index');         // List all receptionists
    Route::get('/create', [ReceptionistsController::class, 'create'])->name('create'); // Show create form
    Route::post('/', [ReceptionistsController::class, 'store'])->name('store');        // Store a new receptionist
    Route::get('/{user}', [ReceptionistsController::class, 'show'])->name('show');     // Show a specific receptionist
    Route::get('/{user}/edit', [ReceptionistsController::class, 'edit'])->name('edit'); // Edit receptionist form
    Route::put('/{user}', [ReceptionistsController::class, 'update'])->name('update');  // Update a receptionist
    Route::delete('/{user}', [ReceptionistsController::class, 'destroy'])->name('destroy'); // Delete a receptionist
});

Route::middleware(['auth', 'permission:manage clients'])->prefix('admin/users/clients')->name('admin.users.clients.')->group(function () {
    Route::get('/', [EssamClientController::class, 'index'])->name('index');        // List all clients
    Route::get('/create', [EssamClientController::class, 'create'])->name('create'); // Show create form
    Route::post('/', [EssamClientController::class, 'store'])->name('store');        // Store a new client
    Route::get('/{user}', [EssamClientController::class, 'show'])->name('show');     // Show a specific client
    Route::get('/{user}/edit', [EssamClientController::class, 'edit'])->name('edit'); // Edit client form
    Route::put('/{user}', [EssamClientController::class, 'update'])->name('update');  // Update a client
    Route::delete('/{user}', [EssamClientController::class, 'destroy'])->name('destroy'); // Delete a client
    Route::patch('/{user}/approve', [EssamClientController::class, 'approve'])->name('approve'); // Approve Client
});


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// Route::middleware(['auth', 'permission:manage clients'])
//     ->prefix('receptionist')
//     ->name('receptionist.')
//     ->group(function () {
//         // Client management routes
//         Route::get('clients/pending', [ClientController::class, 'pendingClients'])->name('clients.pending');
//         Route::get('clients/approved', [ClientController::class, 'approvedClients'])->name('clients.approved');
//         Route::post('clients/{id}/approve', [ClientController::class, 'approveClient'])->name('clients.approve');
//         Route::get('clients/reservations', [ClientController::class, 'clientReservations'])->name('clients.reservations');
//         Route::get('clients/{id}/reservations', [ClientController::class, 'clientReservations'])->name('clients.client-reservations');
//     });

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

Route::middleware(['auth', 'permission:pay for reservations'])->group(function () {
    Route::get('/reservations/checkout/{reservation_id}', [StripeController::class, 'checkout'])->name('stripe.checkout');
    Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
    Route::get('/stripe/cancel/{reservation_id?}', [StripeController::class, 'cancel'])->name('stripe.cancel');
});

// no web or csrf middleware to accept post requests from stripe with no problems, we can later move this route to api.php file and adjust success url in stripe controller if needed
Route::withoutMiddleware(['web', 'csrf'])->post('/webhook/stripe', [StripeController::class, 'handleWebhook'])->name('stripe.webhook');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';


/*Manage Conflicts*/ // ==>>> Essan Controller

// Route::middleware(['auth', 'role:admin'])->prefix('admin/users/clients')->name('admin.users.clients.')->group(function () {
//     Route::get('/', [ManageClientsController::class, 'index'])->name('index');        // List all clients
//     Route::get('/create', [ManageClientsController::class, 'create'])->name('create'); // Show create form
//     Route::post('/', [ManageClientsController::class, 'store'])->name('store');        // Store a new client
//     Route::get('/{user}', [ManageClientsController::class, 'show'])->name('show');     // Show a specific client
//     Route::get('/{user}/edit', [ManageClientsController::class, 'edit'])->name('edit'); // Edit client form
//     Route::put('/{user}', [ManageClientsController::class, 'update'])->name('update');  // Update a client
//     Route::delete('/{user}', [ManageClientsController::class, 'destroy'])->name('destroy'); // Delete a client
// });
