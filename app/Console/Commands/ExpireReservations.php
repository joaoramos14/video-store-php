<?php

namespace App\Console\Commands;

use App\Services\RentalService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'rentals:expire-reservations';

    protected $description = 'Expira reservas não retiradas no prazo e devolve as cópias ao estoque';

    public function handle(RentalService $rentals): int
    {
        $count = $rentals->expireLapsedReservations();

        $this->info("{$count} reserva(s) expirada(s).");

        return self::SUCCESS;
    }
}
