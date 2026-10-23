<?php

use Illuminate\Support\Facades\Schedule;

// Expira reservas que ninguém retirou no prazo e devolve a cópia ao estoque (rodar com: php artisan schedule:work)
Schedule::command('rentals:expire-reservations')->everyFiveMinutes()->withoutOverlapping();
