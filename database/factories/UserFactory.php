<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'client',
            'last_login_at' => now()->subDays(rand(1, 30)), 
            'national_id' => $this->faker->unique()->numerify('##############'),
            'avatar_img' => $this->faker->imageUrl(100, 100, 'people'), 
            'country' => $this->faker->country(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'is_banned' => false,
            'is_approved' => true, 
            'manager_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    // public function configure()
    // {
    //     return $this->afterCreating(function (User $user) {
    //         $roles = ['manager', 'receptionist', 'client'];
    //         $randomRole = $this->faker->randomElement($roles);

    //         $user->assignRole($randomRole);
    //     });
    // }
}
