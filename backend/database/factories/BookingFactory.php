<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->client(),
            'flight_id' => Flight::factory(),
            'status' => $this->faker->randomElement(['confirmed', 'cancelled', 'pending']),
            'booking_reference' => Booking::generateBookingReference(),
            'seat_number' => $this->faker->numberBetween(1, 200),
        ];
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
            ];
        });
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }
}
