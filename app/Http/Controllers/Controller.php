<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

abstract class Controller
{
    //
    protected function getAdminMenuLinks()
    {
        return [
            ['title' => 'Manage Managers', 'href' => route('admin.users.managers.index')],
            ['title' => 'Manage Receptionists', 'href' => route('admin.users.receptionists.index')],
            ['title' => 'Manage Clients', 'href' => route('receptionist.clients.index')],
            ['title' => 'Manage Floors', 'href' => route('floors.index')],
            ['title' => 'Manage Rooms', 'href' => route('rooms.index')]
        ];
    }
    protected function getManagerMenuLinks()
    {
        return [
            ['title' => 'Manage Receptionists', 'href' => route('admin.users.receptionists.index')],
            ['title' => 'Manage Floors', 'href' => route('floors.index')],
            ['title' => 'Manage Rooms', 'href' => route('rooms.index')],
            ['title' => 'Manage Clients', 'href' => route('receptionist.clients.index')],
        ];
    }
    protected function getreceptionistMenuLinks()
    {
        return [
            ['title' => 'Manage Reservations', 'href' => route('admin.users.receptionists.index')],
            ['title' => 'Manage Floors', 'href' => route('floors.index')],
            ['title' => 'Manage Rooms', 'href' => route('rooms.index')],
            ['title' => 'Manage Clients', 'href' => route('receptionist.clients.index')],
        ];
    }
}
