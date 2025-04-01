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
            //render to the welcom page for the admin 
            return Inertia::render('Admin/Dashboard', [
                'total_managers' => User::role('manager')->count(),
                'total_clients' => User::role('client')->count(),
                'total_receptionists' => User::role('receptionist')->count(),
                'total_reservations' => Reservation::count(),
                'total_revenue' => Reservation::sum('price_paid'),
                'baned_users'=> User::where('is_banned', true)->count(),
                'approved_clients'=> User::where('is_approved', true)->count(),
                'pending_clients'=> User::where('is_approved', false)
        ->whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', ['manager', 'receptionist', 'admin']);
        })
        ->count(),
                'menuLinks' => $this->getAdminMenuLinks(),
            ]);
        }
        if ($user->hasRole('manager')) {
            return Inertia::render('Admin/Dashboard',[
                'total_clients' => User::role('client')->get()->count(),
                'total_receptionists' => User::role('receptionist')->count(),
                'total_reservations' => Reservation::count(),
                'approved_clients'=> User::where('is_approved', true)->count(),
                // 'my_receptionists' => User::where('manager_id', $user->id)->count(),
                // 'total_reservations' => Reservation::where('manager_id', $user->id)->count(),
                'my_floors' => Floor::where('manager_id', $user->id)->count(),
                'my_rooms' => Room::where('manager_id', $user->id)->count(),
                'menuLinks' => $this->getManagerMenuLinks(),
            ]);
        }
        if ($user->hasRole('receptionist')) {
            return Inertia::render('Admin/Dashboard',[
                'pending_clients' => User::role('client')->where('is_approved', false)->get()->count(),
                'approved_clients' => User::role('client')->where('is_approved', true)->get()->count(),
                'total_clients' => User::role('client')->get()->count(),
                'total_reservations' => Reservation::count(),
                'menuLinks' => $this->getreceptionistMenuLinks()

            ]);
        }
        if ($user->hasRole('client')) {
            return Inertia::render('Client/landing',[
                'rooms' => Room::where('status', 'available')->get(),
            ]);
        }
        
    }
}
