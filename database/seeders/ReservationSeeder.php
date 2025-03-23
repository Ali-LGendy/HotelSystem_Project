<?php

namespace Database\Seeders;

use App\Models\Reservation;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run()
    {
        // Create 20 reservations using the factory
        Reservation::factory()->count(20)->create();
    }
}
