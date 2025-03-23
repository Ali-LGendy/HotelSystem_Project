<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


// this is an example on how to authorize based on permissions // our practice when it comes to Authorization : permisson gets assigned to roles, roles gets assigned to users then in middleware check permission names, or sometimes role-names
// to add/remove/edit permissions or roles modify the ./database/seeders/RolesAndPermissionsSeeder
// more can be found @ https://spatie.be/docs/laravel-permission/v6/best-practices/roles-vs-permissions
Route::get('/floors', function () {
    return Inertia::render('Welcome');
})->middleware(['permission:manage floors'])->name('floors.index');


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


/* Client Routes */

use App\Http\Controllers\ClientController;

Route::middleware(['auth'])->group(function () {
    Route::get('/clients', [ClientController::class, 'index'])
        ->middleware('permission:manage clients')
        ->name('clients.index'); // List Clients

    Route::get('/clients/approved', [ClientController::class, 'indexApproved'])
        ->middleware('role:receptionist')
        ->name('clients.showApproved'); // Show Receptionist's Approved Clients

    Route::get('/clients/create', [ClientController::class, 'create'])
        ->middleware('permission:manage clients')
        ->name('clients.create'); // Show Client Registration Form

    Route::post('/clients', [ClientController::class, 'store'])
        ->middleware('permission:manage clients')
        ->name('clients.store'); // Register Client

    Route::get('/clients/{client}', [ClientController::class, 'show'])
        ->middleware('permission:manage clients')
        ->name('clients.show'); // Show Client Details

    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])
        ->middleware('permission:manage clients')
        ->name('clients.edit'); // Show Edit Form

    Route::put('/clients/{client}', [ClientController::class, 'update'])
        ->middleware('permission:manage clients')
        ->name('clients.update'); // Update Client

    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])
        ->middleware('permission:manage clients')
        ->name('clients.destroy'); // Delete Client

    Route::patch('/clients/{client}/approve', [ClientController::class, 'approve'])
        ->middleware('permission:approve clients')
        ->name('clients.approve'); // Approve Client
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
