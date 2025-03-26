<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

abstract class Controller
{
    //
    protected function getAdminMenuLinks()
    {
        return [
           ['name' => 'Manage Managers', 'route' => route('admin.users.managers.index')],
            ['name' => 'Manage Receptionists', 'route' => route('admin.users.receptionists.index')],
            ['name' => 'Manage Clients', 'route' => route('admin.users.clients.index')],
                ['name' => 'Manage Floors', 'route' => route('floors.index')],

        ];
    }
}
