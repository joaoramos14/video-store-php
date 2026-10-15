<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalFactory extends Factory
{
    /** Aluguel já retirado e dentro do prazo (estado padrão). */
    public function definition(): array
    {
        $days     = $this->faker->numberBetween(1, 7);
        $rentedAt = now()->subDays($this->faker->numberBetween(0, 3));

        return [
            'user_id'        => User::factory(),
            'movie_id'       => Movie::factory(),
            'reserved_until' => null,
            'rented_at'      => $rentedAt,
            'due_at'         => $rentedAt->copy()->addDays($days),
            'days'           => $days,
            'total_price'    => $this->faker->randomFloat(2, 5, 100),
            'late_fee'       => 0,
            'status'         => Rental::ACTIVE,
        ];
    }

    /** Reserva feita pelo site, ainda dentro do prazo de retirada. O prazo de devolução só começa na retirada. */
    public function reserved(): static
    {
        return $this->state(fn() => [
            'reserved_until' => now()->addHours((int) config('rentals.reservation_hours', 24)),
            'rented_at'      => null,
            'due_at'         => null,
            'status'         => Rental::RESERVED,
        ]);
    }

    /** Reserva cujo prazo de retirada passou e que ainda não foi marcada como expirada. */
    public function lapsed(): static
    {
        return $this->reserved()->state(fn() => [
            'reserved_until' => now()->subHour(),
        ]);
    }

    /** Reserva que o cliente cancelou. */
    public function cancelled(): static
    {
        return $this->reserved()->state(fn() => ['status' => Rental::CANCELLED]);
    }

    /** Reserva que ninguém retirou a tempo. */
    public function expired(): static
    {
        return $this->lapsed()->state(fn() => ['status' => Rental::EXPIRED]);
    }

    /** Devolvido no prazo. */
    public function returned(): static
    {
        return $this->state(fn(array $a) => [
            'status'      => Rental::RETURNED,
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
            'status'    => Rental::LATE,
        ]);
    }
}
