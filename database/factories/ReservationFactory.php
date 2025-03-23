<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition()
    {
        return [
            'accompany_number' => $this->faker->numberBetween(1, 4),
            'price_paid' => $this->faker->numberBetween(10000, 50000), // Price in cents
            'check_in_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'check_out_date' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
            // Updated status values to match migration constraints
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'client_id' => User::inRandomOrder()->first()->id, // Assuming clients are users
            'room_id' => Room::inRandomOrder()->first()->id, // Random room
            'receptionist_id' => 46, // Set receptionist_id to 46 for all
        ];
    }
}
