<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovedClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all receptionists
        $receptionists = User::role('receptionist')->get();
        
        if ($receptionists->isEmpty()) {
            // Create a receptionist if none exists
            $receptionist = User::factory()->receptionist()->create();
            $receptionists = collect([$receptionist]);
        }
        
        // Create 10 approved clients for each receptionist
        foreach ($receptionists as $receptionist) {
            // Create clients that are already approved
            User::factory(5)->create([
                'manager_id' => $receptionist->id,
                'is_approved' => true,
            ])->each(function ($client) {
                $client->assignRole('client');
            });
        }
        
        $this->command->info('Created approved clients for each receptionist.');
    }
}