<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User; // Import the User model
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition()
    {
        return [
            'room_number' => $this->faker->unique()->numerify('R####'), // Ensure unique room numbers
            'floor_id' => $this->faker->numberBetween(1, 5), // Assuming 5 floors
            'room_capacity' => $this->faker->numberBetween(1, 4),
            'price' => $this->faker->numberBetween(10000, 50000), // Price in cents
            'status' => $this->faker->randomElement(['available', 'occupied', 'maintenance']),
            'manager_id' => User::inRandomOrder()->first()->id ?? null, // Assign a random manager or null
        ];
    }
}
