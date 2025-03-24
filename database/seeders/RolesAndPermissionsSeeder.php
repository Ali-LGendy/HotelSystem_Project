<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $receptionist = Role::create(['name' => 'receptionist']);
        $client = Role::create(['name' => 'client']);

        // Define permissions
        $PermissionNames = [
            // User Management
            'manage users',             // Admin
            'manage managers',          // Admin
            'manage receptionists',     // Admin, Manager
            'manage clients',           // Admin
            'assign roles',             // Admin
            'ban users',                // Admin, Manager

            // Floor & Room Management
            'manage floors',            // Admin, Manager
            'manage rooms',             // Admin, Manager

            // Client & Receptionist Management
            'approve clients',          // Admin, Receptionist
            'view all clients',         // Admin, Manager
            'view approved clients',    // Admin, Receptionist

            // Reservations & Payments
            'manage reservations',      // Admin, Manager, Receptionist
            'view client reservations', // Admin, Receptionist
            'make reservations',        // Client
            'pay for reservations',     // Client

            // Reports & System
            'manage payments',          // Admin, Manager, Receptionist
            'view reports',             // Admin, Manager

            // Profile & General Actions
            'update profile',           // Admin, Manager, Receptionist, Client
            'view own reservations',    // Client
        ];

        foreach ($PermissionNames as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => config('auth.defaults.guard')]);
        }

        $admin->syncPermissions($PermissionNames); // remove client permissions?
        $manager->syncPermissions([
            'manage receptionists',
            'ban users',
            'manage floors',
            'manage rooms',
            'view all clients',
            'manage reservations',
            'manage payments',
            'view reports',
            'update profile',
        ]);
        $receptionist->syncPermissions([
            'approve clients',
            'view approved clients',
            'view client reservations',
            'manage reservations',
            'manage payments',
            'update profile',
        ]);
        $client->syncPermissions([
            'make reservations',
            'pay for reservations',
            'view own reservations',
            'update profile',
        ]);
    }
}
