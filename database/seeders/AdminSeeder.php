<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('123456'),
                'national_id' => 123451234512,
                'gender' => 'male',
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    
    }
}
