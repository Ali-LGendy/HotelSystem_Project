<?php

use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagersController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\RoomController;
use App\Models\Floor;
use App\Http\Controllers\ReceptionistsController;



Route::get('/', [RoomController::class, 'clientIndex'])->name('home');

// Public Client Routes (no authentication required)
Route::prefix('hotel')->name('hotel.')->group(function () {
    Route::get('/', [RoomController::class, 'clientIndex'])->name('landing');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
});

    
Route::middleware(['auth','CkeckBan','CkeckApproval'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware(['auth','CkeckBan','CkeckApproval'])->get('/clientReservation', [ClientController::class, 'index'])->name('clientReservation');



Route::middleware(['auth','permission:manage managers','web'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/', [ManagersController::class, 'index'])->name('index');        // List all managers
    Route::get('/create', [ManagersController::class, 'create'])->name('create'); // Show create form
    Route::post('/', [ManagersController::class, 'store'])->name('store');        // Store a new manager
    Route::get('/{user}', [ManagersController::class, 'show'])->name('show');     // Show specific manager
    Route::get('/{user}/edit', [ManagersController::class, 'edit'])->name('edit'); // Edit manager form
    Route::put('/{user}', [ManagersController::class, 'update'])->name('update');  // Update manager
    Route::delete('/{user}', [ManagersController::class, 'destroy'])->name('destroy'); // Delete manager
    Route::patch('/{user}/ban', [ManagersController::class, 'ban'])->name('ban'); // Delete manager
});


Route::middleware(['auth','CkeckBan','CkeckApproval'])
    ->get('clients/myreservation', [ClientController::class, 'showMyReservation'])
    ->name('clients.myreservation');

Route::middleware(['auth','CkeckBan', 'permission:manage receptionists'])->prefix('receptionist')->name('receptionist.')->group(function () {
    Route::get('/', [ReceptionistsController::class, 'index'])->name('index');         
    Route::get('/create', [ReceptionistsController::class, 'create'])->name('create'); 
    Route::post('/', [ReceptionistsController::class, 'store'])->name('store');        
    Route::get('/{user}', [ReceptionistsController::class, 'show'])->name('show');     
    Route::get('/{user}/edit', [ReceptionistsController::class, 'edit'])->name('edit'); 
    Route::put('/{user}', [ReceptionistsController::class, 'update'])->name('update');  
    Route::delete('/{user}', [ReceptionistsController::class, 'destroy'])->name('destroy'); 
     Route::patch('/{user}/ban', [ReceptionistsController::class, 'ban'])->name('ban');
});





// Client Routes
Route::middleware(['auth','CkeckBan','CkeckApproval' ,'permission:manage reservations'])->prefix('client')->name('client.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::get('/clientsReservations', [ClientController::class, 'clientsReservations'])->name('clientsReservations');
    Route::get('/approved', [ClientController::class, 'myApproved'])->name('myApproved');
    Route::get('/create', [ClientController::class, 'create'])->name('create');
    Route::post('/', [ClientController::class, 'store'])->name('store');
    Route::get('/{user}', [ClientController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [ClientController::class, 'edit'])->name('edit'); 
    Route::put('/{user}', [ClientController::class, 'update'])->name('update');
    Route::delete('/{user}', [ClientController::class, 'destroy'])->name('destroy'); 
    Route::patch('/{user}/approve', [ClientController::class, 'approve'])->name('approve');
});







// Stripe Payment Routes
Route::middleware(['auth','CkeckBan','CkeckApproval', 'permission:pay for reservations'])->group(function () {
    Route::get('/reservations/checkout/{reservation_id}', [StripeController::class, 'checkout'])->name('stripe.checkout');
    Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
    Route::get('/stripe/cancel/{reservation_id?}', [StripeController::class, 'cancel'])->name('stripe.cancel');
});

// Stripe Webhook (no web or csrf middleware to accept post requests from stripe)
Route::withoutMiddleware(['web', 'csrf'])->post('/webhook/stripe', [StripeController::class, 'handleWebhook'])->name('stripe.webhook');

Route::group(['middleware' => ['auth','CkeckBan','CkeckApproval', 'permission:manage floors']], function () {
    Route::get('/floors', [FloorController::class, 'index'])->name('floors.index');
    Route::get('/floors/create', [FloorController::class, 'create'])->name('floors.create');
    Route::post('/floors', [FloorController::class, 'store'])->name('floors.store');
    Route::get('/floors/{floor}/edit', [FloorController::class, 'edit'])->name('floors.edit');
    Route::put('/floors/{floor}', [FloorController::class, 'update'])->name('floors.update');
    Route::delete('/floors/{floor}', [FloorController::class, 'destroy'])->name('floors.destroy');
});

Route::group(['middleware' => ['auth','CkeckBan','CkeckApproval', 'permission:manage rooms']], function () {
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
});





require __DIR__.'/settings.php';
require __DIR__.'/auth.php';



