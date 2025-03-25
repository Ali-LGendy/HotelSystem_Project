<?php

namespace Database\Factories;

use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $roomCount = 1000; // Start from 1000 to ensure 4 digits
        $roomCount++;

        // Get or create a floor
        $floor = Floor::inRandomOrder()->first() ?? Floor::factory()->create();

        return [
            'floor_id' => $floor->id,
            'manager_id' => $floor->manager_id,
            'room_number' => $roomCount,
            'room_capacity' => $this->faker->numberBetween(1, 6),
            'price' => $this->faker->numberBetween(1000, 10000),
            'status' => 'available',
        ];
    }
}
