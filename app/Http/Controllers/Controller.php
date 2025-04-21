<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

abstract class Controller
{
    //
    protected function getAdminMenuLinks()
    {
        return [
            ['title' => 'Managers', 'href' => route('manager.index'), 'icon' => 'UserCog'],
            ['title' => 'Receptionists', 'href' => route('receptionist.index'), 'icon' => 'UserPlus'],
            ['title' => 'Clients', 'href' => route('client.index'), 'icon' => 'Users'],
            ['title' => 'Floors', 'href' => route('floors.index'), 'icon' => 'Building2'],
            ['title' => 'Rooms', 'href' => route('rooms.index'), 'icon' => 'DoorClosed'],
        ];
    }

    protected function getManagerMenuLinks()
    {
        return [
            ['title' => 'Receptionists', 'href' => route('receptionist.index'), 'icon' => 'UserPlus'],
            ['title' => 'Clients', 'href' => route('client.index'), 'icon' => 'Users'],
            ['title' => 'Floors', 'href' => route('floors.index'), 'icon' => 'Building2'],
            ['title' => 'Rooms', 'href' => route('rooms.index'), 'icon' => 'DoorClosed'],
        ];
    }

    protected function getreceptionistMenuLinks()
    {
        return [
            ['title' => 'Clients', 'href' => route('client.index'), 'icon' => 'Users'],
            ['title' => 'My Approved Clients', 'href' => route('client.myApproved'), 'icon' => 'UserCheck'],
            ['title' => 'Reservations', 'href' => route('client.clientsReservations'), 'icon' => 'CalendarCheck'],
        ];
    }

}
