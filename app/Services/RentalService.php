<?php

namespace App\Services;

use App\Exceptions\RentalException;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Regras do ciclo de vida do aluguel.
 *
 * Estoque: a reserva já separa uma cópia (-1). Cancelar, expirar ou devolver devolve essa cópia (+1).
 * Retirar não mexe no estoque, porque a cópia já estava separada.
 */
class RentalService
{
    /** Cliente reserva o filme pelo site. O prazo do aluguel só começa a contar na retirada. */
    public function reserve(User $user, Movie $movie, int $days): Rental
    {
        // A transação com lock no filme impede que duas pessoas reservem a última cópia ao mesmo tempo
        return DB::transaction(function () use ($user, $movie, $days) {
            $locked = Movie::lockForUpdate()->findOrFail($movie->id);

            $alreadyHasIt = Rental::where('user_id', $user->id)
                ->where('movie_id', $locked->id)
                ->blocking()
                ->exists();

            if ($alreadyHasIt) {
                throw new RentalException('Você já tem uma reserva ou um aluguel em andamento deste filme.');
            }

            if ($locked->stock <= 0) {
                throw new RentalException('Filme indisponível no estoque no momento.');
            }

            $rental = Rental::create([
                'user_id'        => $user->id,
                'movie_id'       => $locked->id,
                'days'           => $days,
                'total_price'    => $locked->daily_price * $days,
                'reserved_until' => now()->addHours(config('rentals.reservation_hours')),
                'status'         => Rental::RESERVED,
            ]);

            $locked->decrement('stock');

            return $rental;
        });
    }

    /** Cliente desiste da reserva antes de retirar. */
    public function cancel(Rental $rental): Rental
    {
        if (!$rental->isReserved()) {
            throw new RentalException('Só é possível cancelar uma reserva que ainda não foi retirada.');
        }

        $this->closeReservation($rental, Rental::CANCELLED);

        return $rental;
    }

    /** Balcão confirma a retirada: é aqui que o prazo de devolução começa a contar. */
    public function pickup(Rental $rental): Rental
    {
        if (!$rental->isReserved()) {
            throw new RentalException(match ($rental->status) {
                Rental::CANCELLED => 'Esta reserva foi cancelada.',
                Rental::EXPIRED   => 'Esta reserva expirou.',
                default           => 'Este filme já foi retirado.',
            });
        }

        // Fora do prazo só recusa; quem expira a reserva e devolve a cópia é o comando agendado
        if (!$rental->isPickupOpen()) {
            throw new RentalException('O prazo de retirada desta reserva terminou.');
        }

        $now = now();
        $rental->update([
            'rented_at' => $now,
            'due_at'    => $now->copy()->addDays($rental->days),
            'status'    => Rental::ACTIVE,
        ]);

        return $rental;
    }

    /** Balcão registra a devolução e calcula a multa, se houver atraso. */
    public function giveBack(Rental $rental): Rental
    {
        if ($rental->status === Rental::RETURNED) {
            throw new RentalException('Este filme já foi devolvido.');
        }

        if (!$rental->isOut()) {
            throw new RentalException(
                $rental->isReserved()
                    ? 'Este filme ainda não foi retirado. Confirme a retirada antes de registrar a devolução.'
                    : 'Este aluguel foi encerrado sem retirada.'
            );
        }

        $now = now();
        $lateFee = 0;

        if ($now->greaterThan($rental->due_at)) {
            // Cada dia (ou fração) de atraso conta como um dia
            $daysLate = (int) ceil($rental->due_at->diffInSeconds($now) / 86400);
            // Multa: diária paga no aluguel * dias de atraso * 1.5
            $dailyPrice = $rental->total_price / max($rental->days, 1);
            $lateFee = round($daysLate * $dailyPrice * 1.5, 2);
        }

        $rental->update([
            'returned_at' => $now,
            'late_fee'    => $lateFee,
            'status'      => Rental::RETURNED,
        ]);

        // O movie é carregado do banco na primeira vez que é acessado
        $rental->movie->increment('stock');

        return $rental;
    }

    /** Expira as reservas que passaram do prazo e devolve as cópias ao estoque. Retorna quantas expirou. */
    public function expireLapsedReservations(): int
    {
        $rentals = Rental::lapsedReservations()->get();

        foreach ($rentals as $rental) {
            $this->closeReservation($rental, Rental::EXPIRED);
        }

        return $rentals->count();
    }

    private function closeReservation(Rental $rental, string $status): void
    {
        $rental->update(['status' => $status]);
        $rental->movie()->increment('stock');
    }
}
