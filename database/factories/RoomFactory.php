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
            'image' => $this->faker->randomElement([
                'storage/assets/rooms/1.jpg',
                'storage/assets/rooms/2.jpg',
                'storage/assets/rooms/3.jpg',
                'storage/assets/rooms/4.jpg',
                'storage/assets/rooms/5.jpg',
            ]),
            'room_number' => $roomCount,
            'room_capacity' => $this->faker->numberBetween(1, 6),
            'price' => $this->faker->numberBetween(10, 100) * 100,
            'status' => 'available',
        ];
    }
}
