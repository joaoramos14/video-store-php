<?php

namespace Database\Factories;

use App\Models\Rental;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rental_id' => Rental::factory()->returned(),
            'user_id'   => fn(array $a) => Rental::find($a['rental_id'])->user_id,
            'movie_id'  => fn(array $a) => Rental::find($a['rental_id'])->movie_id,
            'rating'    => $this->faker->numberBetween(1, 5),
            'comment'   => $this->faker->optional()->sentence(),
        ];
    }
}
