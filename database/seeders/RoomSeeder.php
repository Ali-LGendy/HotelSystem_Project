<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        // Create 20 rooms using the factory
        Room::factory()->count(20)->create();
    }
}
