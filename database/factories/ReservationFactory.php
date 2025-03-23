<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get or create an available room
        $room = Room::where('status', 'available')->inRandomOrder()->first()
            ?? Room::factory()->create();
            
        // Random stay duration (1-7 days)
        $stayDuration = $this->faker->numberBetween(1, 7);
        $checkInDate = Carbon::now()->addDays($this->faker->numberBetween(1, 30));
        $checkOutDate = (clone $checkInDate)->addDays($stayDuration);
        
        return [
            'client_id' => User::where('role', 'client')->inRandomOrder()->first()?->id 
                ?? User::factory()->create(['role' => 'client'])->id,
            'room_id' => $room->id,
            'accompany_number' => $this->faker->numberBetween(1, $room->room_capacity),
            'price_paid' => $room->price * $stayDuration,
            'receptionist_id' => User::where('role', 'receptionist')->inRandomOrder()->first()?->id 
                ?? User::factory()->create(['role' => 'receptionist'])->id,
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'status' => 'pending'
        ];
    }
    
    /**
     * Mark reservation as confirmed and update room status
     */
    public function confirmed()
    {
        return $this->state(function (array $attributes) {
            $room = Room::find($attributes['room_id']);
            if ($room) $room->update(['status' => 'occupied']);
            
            return ['status' => 'confirmed'];
        });
    }
    
    /**
     * Mark reservation as checked in and update room status
     */
    public function checkedIn()
    {
        return $this->state(function (array $attributes) {
            $room = Room::find($attributes['room_id']);
            if ($room) $room->update(['status' => 'occupied']);
            
            return [
                'status' => 'checked_in',
                'check_in_date' => Carbon::now()->subDays(1),
                'check_out_date' => Carbon::now()->addDays(5),
            ];
        });
    }
}
