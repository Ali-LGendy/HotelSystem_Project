<?php

// use App\Http\Controllers\Receptionist\ClientController;

use App\Http\Controllers\Receptionist\ClientController;
use App\Http\Controllers\Receptionist\ReservationController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagersController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\RoomController;
use App\Models\Floor;
use App\Http\Controllers\ReceptionistsController;

// this is an example on how to authorize based on permissions // our practice when it comes to Authorization : permisson gets assigned to roles, roles gets assigned to users then in middleware check permission names, or sometimes role-names
// to add/remove/edit permissions or roles modify the ./database/seeders/RolesAndPermissionsSeeder
// more can be found @ https://spatie.be/docs/laravel-permission/v6/best-practices/roles-vs-permissions

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Public Client Routes (no authentication required)
Route::prefix('hotel')->name('hotel.')->group(function () {
    Route::get('/', [RoomController::class, 'clientIndex'])->name('landing');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
});

    
Route::middleware(['auth','CkeckBan','CkeckApproval'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');



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


Route::middleware(['auth','CkeckBan', 'permission:manage receptionists'])->prefix('admin/users/receptionists')->name('admin.users.receptionists.')->group(function () {
    Route::get('/', [ReceptionistsController::class, 'index'])->name('index');         
    Route::get('/create', [ReceptionistsController::class, 'create'])->name('create'); 
    Route::post('/', [ReceptionistsController::class, 'store'])->name('store');        
    Route::get('/{user}', [ReceptionistsController::class, 'show'])->name('show');     
    Route::get('/{user}/edit', [ReceptionistsController::class, 'edit'])->name('edit'); 
    Route::put('/{user}', [ReceptionistsController::class, 'update'])->name('update');  
    Route::delete('/{user}', [ReceptionistsController::class, 'destroy'])->name('destroy'); 
     Route::patch('/{user}/ban', [ReceptionistsController::class, 'ban'])->name('ban');
});




// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


// Receptionist Routes
Route::middleware(['auth', 'permission:manage reservations'])
    ->prefix('receptionist')
    ->name('receptionist.')
    ->group(function () {
        // Use resource routing for ReservationController (except create and store)
        Route::resource('reservations', ReservationController::class)->except(['create', 'store']);
        // Add route for all reservations
        Route::get('all-reservations', [ReservationController::class, 'allReservations'])->name('reservations.all');
        Route::get('clients/{id}/reservations', [ClientController::class, 'clientReservations'])->name('clients.client-reservations');

        // Client management routes
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/my-approved', [ClientController::class, 'myApprovedClients'])->name('clients.my-approved');
        Route::get('clients/all', [ClientController::class, 'allClients'])->name('clients.all');
        Route::get('clients/reservations/{id?}', [ClientController::class, 'clientReservations'])->name('clients.reservations');

        // Client approval/rejection routes
        Route::post('clients/{id}/approve', [ClientController::class, 'approveClient'])->name('clients.approve');
        Route::post('clients/{id}/reject', [ClientController::class, 'rejectClient'])->name('clients.reject');

        // Client ban/unban routes
        Route::post('clients/{id}/ban', [ClientController::class, 'banClient'])->name('clients.ban');
        Route::post('clients/{id}/unban', [ClientController::class, 'unbanClient'])->name('clients.unban');

        // API endpoint for client approval
        Route::post('api/clients/{id}/approve', [ClientController::class, 'approveClientApi'])->name('api.clients.approve');

        // Admin-only client management routes
        Route::middleware('role:admin')->group(function () {
            Route::get('clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
            Route::put('clients/{id}', [ClientController::class, 'update'])->name('clients.update');
            Route::delete('clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');
        });
    });

// Client Routes have been removed

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

Route::group(['middleware' => ['auth', 'permission:manage rooms']], function () {
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
});

// can be remove at deploy
Route::get('/debug/floors', function() {
    $floors = Floor::with('manager')->get();
    $user = auth()->user();
    return response()->json($user);
});




require __DIR__.'/settings.php';
require __DIR__.'/auth.php';



