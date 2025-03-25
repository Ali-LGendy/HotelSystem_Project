<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Floor>
 */
class FloorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $floorNumber = 1000; // Start from 1000 to ensure 4 digits
        $floorNumber++;

        return [
            'name' => 'Floor '.$floorNumber,
            'number' => $floorNumber,
            'manager_id' => User::role('manager')->inRandomOrder()->first()?->id
                ?? User::factory()->manager()->create()->id,
        ];
    }
}
