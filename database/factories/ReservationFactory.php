<?php

namespace Database\Factories;

use App\Models\TableSeat;
use App\Models\User;
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
        return [
            'user_id' => User::factory(),
            'table_id' => TableSeat::factory(),
            'datetime' => $this->faker->dateTimeBetween('now', '+1 week'),
            'info' => $this->faker->sentence(),
            'verification_code' => $this->faker->unique()->numberBetween(100000, 999999),
            'status' => 'ongoing'
        ];
    }
}
