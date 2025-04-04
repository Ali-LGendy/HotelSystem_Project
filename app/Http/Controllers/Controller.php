<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

abstract class Controller
{
    //
    protected function getAdminMenuLinks()
    {
        return [
            ['title' => 'Client Interface', 'href' => route('hotel.landing')],
            ['title' => 'Manage Managers', 'href' => route('manager.index')],
            ['title' => 'Manage Receptionists', 'href' => route('receptionist.index')],
            // ['title' => 'Manage Clients', 'href' => route('receptionist.clients.index')],
            ['title' => 'Manage Clients', 'href' => route('client.index')],
            ['title' => 'Manage Floors', 'href' => route('floors.index')],
            ['title' => 'Manage Rooms', 'href' => route('rooms.index')]
        ];
    }
    protected function getManagerMenuLinks()
    {
        return [
            ['title' => 'Client Interface', 'href' => route('hotel.landing')],
            ['title' => 'Manage Receptionists', 'href' => route('receptionist.index')],
            ['title' => 'Manage Clients', 'href' => route('client.index')],
            ['title' => 'Manage Floors', 'href' => route('floors.index')],
            ['title' => 'Manage Rooms', 'href' => route('rooms.index')],
            // ['title' => 'Manage Clients', 'href' => route('receptionist.clients.index')],

        ];
    }
    protected function getreceptionistMenuLinks()
    {
        return [
            ['title' => 'Client Interface', 'href' => route('hotel.landing')],
            ['title' => 'Manage Clients', 'href' => route('client.index')],
            ['title' => 'My Approved Clients', 'href' => route('client.myApproved')],
            // ['title' => 'Manage Clients', 'href' => route('receptionist.clients.index')],
            // ['title' => 'Approved Clients', 'href' => route('receptionist.clients.my-approved')],
            ['title' => 'Clients Reservations', 'href' => route('client.clientsReservations')]
        ];
    }
}
