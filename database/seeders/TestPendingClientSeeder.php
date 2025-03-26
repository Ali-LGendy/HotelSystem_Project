<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestPendingClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test client with pending approval status
        User::create([
            'name' => 'Test Pending Client',
            'email' => 'pending@example.com',
            'password' => Hash::make('password'),
            'national_id' => '12345678901234',
            'mobile' => '1234567890',
            'country' => 'Test Country',
            'gender' => 'male',
            'role' => 'client',
            'is_approved' => false,
        ]);
        
        // Log the creation
        \Log::info('Test pending client created');
        
        // Verify the client exists with the correct status
        $client = User::where('email', 'pending@example.com')->first();
        \Log::info('Test client is_approved value: ' . ($client->is_approved ? 'true' : 'false'));
    }
}