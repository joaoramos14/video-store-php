<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(3),
            'synopsis'    => fake()->paragraph(),
            'year'        => fake()->numberBetween(1980, 2025),
            'duration'    => fake()->numberBetween(80, 180),
            'stock'       => fake()->numberBetween(0, 5),
            'daily_price' => fake()->randomFloat(2, 5, 15),
            'genre_id'    => Genre::factory(),
        ];
    }
}
