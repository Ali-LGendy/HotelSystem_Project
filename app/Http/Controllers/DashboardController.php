<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reservation;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return Inertia::render('Admin/Dashboard', [
                'total_users' => User::count(),
                'total_reservations' => Reservation::count(),
                'total_revenue' => Reservation::sum('price_paid'),
                'menuLinks' => [
                ['name' => 'Manage Managers', 'route' => route('admin.users.managers.store')],
                ['name' => 'Manage Receptionists', 'route' => route('admin.users.receptionists.store')],
                ['name' => 'Manage Floors', 'route' => route('floors.index')],
                // ['name' => 'Manage Clients', 'route' => route('admin.users.clients.store')],
            ]
            ]);
        }
        if ($user->hasRole('manager')) {
            return Inertia::render('Admin/Dashboard',[
                'total_clients' => User::role('client')->get()->count(),
                // 'my_receptionists' => User::where('manager_id', $user->id)->count(),
                // 'total_reservations' => Reservation::where('manager_id', $user->id)->count(),
                // 'total_revenue' => Reservation::where('manager_id', $user->id)->sum('price_paid'),
                // 'my_floors' => Room::where('manager_id', $user->id)->distinct('floor_id')->count(),
                // 'my_rooms' => Room::where('manager_id', $user->id)->count(),
                'menuLinks' => [
                // ['name' => 'Manage Floors', 'route' => route('admin.floors.index')],
                // ['name' => 'Manage Rooms', 'route' => route('admin.rooms.index')],
                // ['name' => 'Manage Reservations', 'route' => route('admin.reservations.index')],
                ['name' => 'Manage Receptionists', 'route' => route('admin.users.receptionists.store')],
                // ['name' => 'Manage Clients', 'route' => route('admin.users.clients.store')],
            ]
            ]);
        }
        if ($user->hasRole('receptionist')) {
            return Inertia::render('Admin/Dashboard',[
                'clients_to_approve' => User::role('client')->where('is_approved', false)->get(),
                'approved_clients' => User::role('client')->where('is_approved', true)->get(),
                // 'total_reservations' => Reservation::where('is_approved', true)->where('manager_id', $user->id),
            ]);
        }
        if ($user->hasRole('client')) {
            return Inertia::render('Admin/Dashboard',[
                'my_reservations' => Reservation::where('client_id', $user->id)->get(),
                'avialable_rooms' => Room::where('status', 'available')->get(),
            ]);
        }
        
    }
}
