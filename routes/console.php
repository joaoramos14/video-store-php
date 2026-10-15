<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Rental;

// Marca como 'late' os aluguéis ativos com prazo vencido (rodar com: php artisan schedule:work)
Schedule::call(function () {
    Rental::where('status', Rental::ACTIVE)->where('due_at', '<', now())->update(['status' => Rental::LATE]);
})->hourly()->name('rentals:mark-late');

// Expira reservas que ninguém retirou no prazo e devolve a cópia ao estoque
Schedule::command('rentals:expire-reservations')->everyFiveMinutes()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
