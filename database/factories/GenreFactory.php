<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

class GenreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->word(),
            'description' => fake()->sentence(),
        ];
    }
}