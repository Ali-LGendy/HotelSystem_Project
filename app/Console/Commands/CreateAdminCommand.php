<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminCommand extends Command
{
    protected $signature = 'create:admin {--name=} {--password=} {--gender=male}';
    protected $description = 'Create a new admin user via CLI';

    public function handle()
    {
        $name = $this->option('name') ?: $this->ask('Enter admin email:');
        $password = $this->option('password') ?: $this->secret('Enter admin password:');
        $gender = $this->option('gender') ?? 'male';


        if (User::where('email', $name)->exists()) {
            $this->error('Admin already exists!');
            return;
        }

        $admin = User::factory()->create([
            'email' => $name,
            'password' => Hash::make($password), // Override factory password
        ]);
        $admin->assignRole('admin');

        $this->info("Admin {$name} created successfully!");
    }
}

