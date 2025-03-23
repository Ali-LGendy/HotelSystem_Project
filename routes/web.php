<?php

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
})->middleware(['permission:manage floors'])->name('floors.index');


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

// Route::middleware(['auth', 'role:admin'])->prefix('admin/users/clients')->name('admin.users.clients.')->group(function () {
//     Route::get('/', [ManageClientsController::class, 'index'])->name('index');        // List all clients
//     Route::get('/create', [ManageClientsController::class, 'create'])->name('create'); // Show create form
//     Route::post('/', [ManageClientsController::class, 'store'])->name('store');        // Store a new client
//     Route::get('/{user}', [ManageClientsController::class, 'show'])->name('show');     // Show a specific client
//     Route::get('/{user}/edit', [ManageClientsController::class, 'edit'])->name('edit'); // Edit client form
//     Route::put('/{user}', [ManageClientsController::class, 'update'])->name('update');  // Update a client
//     Route::delete('/{user}', [ManageClientsController::class, 'destroy'])->name('destroy'); // Delete a client
// });


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
