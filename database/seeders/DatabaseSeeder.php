<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->call([
            RolesAndPermissionsSeeder::class,
            //  RoomSeeder::class, // Uncommented if needed
            //  ReservationSeeder::class,
        ]);
        $this->call([AdminSeeder::class]);

        // Create users
        User::factory(3)->create(['role' => 'manager']);
        User::factory(5)->create(['role' => 'receptionist']);
        User::factory(15)->create(['role' => 'client']);
        
        // Create floors and rooms
        $floors = Floor::factory(5)->create();
        
        // Create rooms for each floor
        foreach ($floors as $floor) {
            Room::factory(6)->create([
                'floor_id' => $floor->id,
                'manager_id' => $floor->manager_id
            ]);
        }
        
        // Create reservations
        Reservation::factory(10)->create(); // Pending
        Reservation::factory(8)->confirmed()->create(); // Confirmed
        Reservation::factory(5)->checkedIn()->create(); // Checked in
    }
}
 // RoomSeeder::class, // Uncommented if needed
            // ReservationSeeder::class,
}
