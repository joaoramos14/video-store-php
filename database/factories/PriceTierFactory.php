<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PriceTierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->word(),
            'daily_price' => fake()->randomFloat(2, 2, 15),
            'description' => null,
        ];
    }
}
