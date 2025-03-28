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
           // WorldSeeder::class,
            //  RoomSeeder::class, // Uncommented if needed
            //  ReservationSeeder::class,
        ]);
        $this->call([AdminSeeder::class]);

        // Create users
        User::factory(20)->manager()->create(['is_approved' => true]);
        User::factory(30)->receptionist()->create(['is_approved' => true]);
        User::factory(70)->client()->create();

        // Create approved clients
        $this->call([ApprovedClientSeeder::class]);

        // Create floors and rooms
        $floors = Floor::factory(10)->create();

        // Create rooms for each floor
        foreach ($floors as $floor) {
            Room::factory(10)->create([
                'floor_id' => $floor->id,
                'manager_id' => $floor->manager_id,
            ]);
        }

        // Create reservations
        Reservation::factory(20)->create(); // Pending
        Reservation::factory(10)->confirmed()->create(); // Confirmed
        Reservation::factory(10)->checkedIn()->create(); // Checked in
    }
}
// RoomSeeder::class, // Uncommented if needed
// ReservationSeeder::class,
