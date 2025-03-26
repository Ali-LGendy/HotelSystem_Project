<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
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
                ['title' => 'Manage Managers', 'href' => route('admin.users.managers.store')],
                ['title' => 'Manage Receptionists', 'href' => route('admin.users.receptionists.store')],
                ['title' => 'Manage Clients', 'href' => route('receptionist.clients.index')],

            ]
            ]);
        }
        if ($user->hasRole('manager')) {
            return Inertia::render('Admin/Dashboard',[
                'total_clients' => User::role('client')->get()->count(),
                'my_receptionists' => User::where('manager_id', $user->id)->count(),
                // 'total_reservations' => Reservation::where('manager_id', $user->id)->count(),
                'total_revenue' => Reservation::where('status','<>', 'pending')->sum('price_paid'),
                'my_floors' => Floor::where('manager_id', $user->id)->count(),
                'my_rooms' => Room::where('manager_id', $user->id)->count(),
                'menuLinks' => [
                ['title' => 'Manage Receptionists', 'href' => route('admin.users.receptionists.index')],
                ['title' => 'Manage Floors', 'href' => route('floors.index')],
                ['title' => 'Manage Rooms', 'href' => route('rooms.index')],
                ['title' => 'Manage Clients', 'href' => route('receptionist.clients.index')],
            ]
            ]);
        }
        if ($user->hasRole('receptionist')) {
            return Inertia::render('Admin/Dashboard',[
                'clients_to_approve' => User::role('client')->where('is_approved', false)->get(),
                'approved_clients' => User::role('client')->where('is_approved', true)->get(),
                'menuLinks' => $this->getreceptionistMenuLinks()
                // 'total_reservations' => Reservation::where('is_approved', true)->where('manager_id', $user->id),
                // ['title' => 'Manage Floors', 'href' => route('floors.index')],
                // ['title' => 'Manage Rooms', 'href' => route('rooms.index')],
                ['title' => 'Manage Clients', 'href' => route('receptionist.clients.index')],
                ['title' => 'My Approved Clients', 'href' => route('receptionist.clients.index')],

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
