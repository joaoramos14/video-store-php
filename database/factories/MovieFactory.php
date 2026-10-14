<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tmdb_id'     => $this->faker->unique()->numberBetween(1000, 999999),
            'title'       => $this->faker->sentence(3),
            'director'    => $this->faker->name(),
            'synopsis'    => $this->faker->paragraph(),
            'year'        => $this->faker->numberBetween(1970, (int) date('Y')),
            'duration'    => $this->faker->numberBetween(80, 180),
            'cover'       => null,
            'daily_price' => $this->faker->randomFloat(2, 5, 25),
            'stock'       => $this->faker->numberBetween(1, 10),
            'genre_id'    => Genre::inRandomOrder()->first()?->id ?? Genre::factory(),
        ];
    }
}
