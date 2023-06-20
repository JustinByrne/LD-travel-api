<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'starting_at' => fake()->dateTimeBetween(startDate: now(), endDate: '+0 days'),
            'ending_at' => fake()->dateTimeBetween(startDate: '+1 days', endDate: '+5 days'),
            'price' => fake()->randomDigitNotZero(),
        ];
    }
}
