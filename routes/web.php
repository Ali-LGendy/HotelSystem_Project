<?php

// use App\Http\Controllers\Receptionist\ClientController;
use App\Http\Controllers\EssamClientController;
use App\Http\Controllers\Client\ClientController as ClientUserController;
use App\Http\Controllers\Client\ReservationController as ClientReservationController;
use App\Http\Controllers\Receptionist\ClientController;
use App\Http\Controllers\Receptionist\ReservationController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ManagersController;
use App\Http\Controllers\ManageReceptionistsController;
use App\Http\Controllers\FloorController;
use App\Models\Floor;
use App\Http\Controllers\ReceptionistsController;

// this is an example on how to authorize based on permissions // our practice when it comes to Authorization : permisson gets assigned to roles, roles gets assigned to users then in middleware check permission names, or sometimes role-names
// to add/remove/edit permissions or roles modify the ./database/seeders/RolesAndPermissionsSeeder
// more can be found @ https://spatie.be/docs/laravel-permission/v6/best-practices/roles-vs-permissions

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

    
Route::middleware(['auth',])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');



Route::middleware(['auth','permission:manage managers','web'])->prefix('admin/users/managers')->name('admin.users.managers.')->group(function () {
    Route::get('/', [ManagersController::class, 'index'])->name('index');        // List all managers
    Route::get('/create', [ManagersController::class, 'create'])->name('create'); // Show create form
    Route::post('/', [ManagersController::class, 'store'])->name('store');        // Store a new manager
    Route::get('/{user}', [ManagersController::class, 'show'])->name('show');     // Show specific manager
    Route::get('/{user}/edit', [ManagersController::class, 'edit'])->name('edit'); // Edit manager form
    Route::put('/{user}', [ManagersController::class, 'update'])->name('update');  // Update manager
    Route::delete('/{user}', [ManagersController::class, 'destroy'])->name('destroy'); // Delete manager
    Route::patch('/{user}/ban', [ManagersController::class, 'ban'])->name('ban'); // Delete manager
});


// Route::middleware(['auth','permission:manage receptionists'])->patch('/admin/users/managers/{user}/ban', [ManagersController::class, 'ban'])
//     ->name('admin.users.managers.ban');


Route::middleware(['auth', 'permission:manage receptionists'])->prefix('admin/users/receptionists')->name('admin.users.receptionists.')->group(function () {
    Route::get('/', [ReceptionistsController::class, 'index'])->name('index');         // List all receptionists
    Route::get('/create', [ReceptionistsController::class, 'create'])->name('create'); // Show create form
    Route::post('/', [ReceptionistsController::class, 'store'])->name('store');        // Store a new receptionist
    Route::get('/{user}', [ReceptionistsController::class, 'show'])->name('show');     // Show a specific receptionist
    Route::get('/{user}/edit', [ReceptionistsController::class, 'edit'])->name('edit'); // Edit receptionist form
    Route::put('/{user}', [ReceptionistsController::class, 'update'])->name('update');  // Update a receptionist
    Route::delete('/{user}', [ReceptionistsController::class, 'destroy'])->name('destroy'); // Delete a receptionist
     Route::patch('/{user}/ban', [ReceptionistsController::class, 'ban'])->name('ban');
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


// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


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

Route::group(['middleware' => ['auth', 'permission:manage floors']], function () {
    Route::get('/floors', [FloorController::class, 'index'])->name('floors.index');
    Route::get('/floors/create', [FloorController::class, 'create'])->name('floors.create');
    Route::post('/floors', [FloorController::class, 'store'])->name('floors.store');
    Route::get('/floors/{floor}/edit', [FloorController::class, 'edit'])->name('floors.edit');
    Route::put('/floors/{floor}', [FloorController::class, 'update'])->name('floors.update');
    Route::delete('/floors/{floor}', [FloorController::class, 'destroy'])->name('floors.destroy');
});

// can be remove at deploy
Route::get('/debug/floors', function() {
    $floors = Floor::with('manager')->get();
    $user = auth()->user();
    return response()->json($user);
});




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
