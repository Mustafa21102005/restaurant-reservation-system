<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TableSeat>
 */
class TableSeatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'capacity' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(['available', 'unavailable', 'reserved']),
        ];
    }
}
