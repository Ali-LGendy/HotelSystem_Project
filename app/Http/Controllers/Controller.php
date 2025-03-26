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
            ['title' => 'Manage Clients', 'href' => route('admin.users.clients.index')],
        ];
    }
    protected function getManagerMenuLinks()
    {
        return [
            ['title' => 'Manage Managers', 'href' => route('admin.users.managers.index')],
            ['title' => 'Manage Receptionists', 'href' => route('admin.users.receptionists.index')],
            ['title' => 'Manage Floors', 'href' => route('admin.users.floors.index')],
            ['title' => 'Manage Rooms', 'href' => route('admin.users.rooms.index')],

        ];
    }
}
