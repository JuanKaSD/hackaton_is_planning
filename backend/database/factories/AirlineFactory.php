<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Airline>
 */
class AirlineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Airlines',
            'code' => strtoupper(fake()->randomLetter() . fake()->randomLetter()),
            'logo' => fake()->imageUrl(200, 200, 'business'),
            'description' => fake()->paragraph(),
            'enterprise_id' => User::factory()->enterprise(),
        ];
    }
}
