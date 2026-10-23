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
        // Libera cópias de reservas vencidas antes de checar estoque e duplicidade
        $this->expireLapsedReservations();

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
        return DB::transaction(function () use ($rental) {
            $locked = Rental::lockForUpdate()->findOrFail($rental->id);

            if (!$locked->isReserved()) {
                throw new RentalException('Só é possível cancelar uma reserva que ainda não foi retirada.');
            }

            $this->closeReservation($locked, Rental::CANCELLED);

            return $locked;
        });
    }

    /** Balcão confirma a retirada: é aqui que o prazo de devolução começa a contar. */
    public function pickup(Rental $rental): Rental
    {
        $lapsed = false;

        $result = DB::transaction(function () use ($rental, &$lapsed) {
            $locked = Rental::lockForUpdate()->findOrFail($rental->id);

            if (!$locked->isReserved()) {
                throw new RentalException(match ($locked->status) {
                    Rental::CANCELLED => 'Esta reserva foi cancelada.',
                    Rental::EXPIRED   => 'Esta reserva expirou.',
                    default           => 'Este filme já foi retirado.',
                });
            }

            if (!$locked->isPickupOpen()) {
                // O aviso é lançado depois do commit; dentro da transação ele desfaria a expiração.
                $this->closeReservation($locked, Rental::EXPIRED);
                $lapsed = true;

                return $locked;
            }

            $now = now();
            $locked->update([
                'rented_at' => $now,
                'due_at'    => $now->copy()->addDays($locked->days),
                'status'    => Rental::ACTIVE,
            ]);

            return $locked;
        });

        if ($lapsed) {
            throw new RentalException('O prazo de retirada desta reserva terminou. A cópia voltou para o estoque.');
        }

        return $result;
    }

    /** Balcão registra a devolução e calcula a multa, se houver atraso. */
    public function giveBack(Rental $rental): Rental
    {
        return DB::transaction(function () use ($rental) {
            $locked = Rental::lockForUpdate()->with('movie')->findOrFail($rental->id);

            if ($locked->status === Rental::RETURNED) {
                throw new RentalException('Este filme já foi devolvido.');
            }

            if (!$locked->isOut()) {
                throw new RentalException(
                    $locked->isReserved()
                        ? 'Este filme ainda não foi retirado. Confirme a retirada antes de registrar a devolução.'
                        : 'Este aluguel foi encerrado sem retirada.'
                );
            }

            $now = now();
            $lateFee = 0;

            if ($now->greaterThan($locked->due_at)) {
                // Cada dia (ou fração) de atraso conta como um dia
                $daysLate = (int) ceil($locked->due_at->diffInSeconds($now) / 86400);
                // Multa: diária paga no aluguel * dias de atraso * 1.5
                $dailyPrice = $locked->total_price / max($locked->days, 1);
                $lateFee = round($daysLate * $dailyPrice * 1.5, 2);
            }

            $locked->update([
                'returned_at' => $now,
                'late_fee'    => $lateFee,
                'status'      => Rental::RETURNED,
            ]);

            $locked->movie->increment('stock');

            return $locked;
        });
    }

    /** Expira as reservas que passaram do prazo e devolve as cópias ao estoque. Retorna quantas expirou. */
    public function expireLapsedReservations(): int
    {
        $expired = 0;

        Rental::lapsedReservations()->pluck('id')->each(function ($id) use (&$expired) {
            DB::transaction(function () use ($id, &$expired) {
                $rental = Rental::lockForUpdate()->find($id);

                // Pode ter sido retirada ou cancelada enquanto esperava o lock
                if ($rental && $rental->isReserved() && !$rental->isPickupOpen()) {
                    $this->closeReservation($rental, Rental::EXPIRED);
                    $expired++;
                }
            });
        });

        return $expired;
    }

    private function closeReservation(Rental $rental, string $status): void
    {
        $rental->update(['status' => $status]);
        $rental->movie()->increment('stock');
    }
}
