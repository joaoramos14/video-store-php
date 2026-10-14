<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalFactory extends Factory
{
    public function definition(): array
    {
        $days     = $this->faker->numberBetween(1, 7);
        $rentedAt = now()->subDays($this->faker->numberBetween(0, 3));
        $movie    = Movie::factory();

        return [
            'user_id'     => User::factory(),
            'movie_id'    => $movie,
            'rented_at'   => $rentedAt,
            'due_at'      => $rentedAt->copy()->addDays($days),
            'days'        => $days,
            'total_price' => $this->faker->randomFloat(2, 5, 100),
            'late_fee'    => 0,
            'status'      => 'active',
        ];
    }

    /** Devolvido no prazo. */
    public function returned(): static
    {
        return $this->state(fn(array $a) => [
            'status'      => 'returned',
            'returned_at' => $a['due_at'],
        ]);
    }

    /** Prazo vencido e ainda não devolvido. */
    public function late(): static
    {
        return $this->state(fn(array $a) => [
            'rented_at' => now()->subDays(10),
            'due_at'    => now()->subDays(3),
            'days'      => 7,
            'status'    => 'late',
        ]);
    }
}
