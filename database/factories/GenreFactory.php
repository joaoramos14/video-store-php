<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GenreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tmdb_id' => fake()->unique()->numberBetween(1, 999999),
            'name'    => fake()->unique()->word(),
        ];
    }
}
