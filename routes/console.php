<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Rental;

// Marca como 'late' os aluguéis ativos com prazo vencido (rodar com: php artisan schedule:work)
Schedule::call(function () {
    Rental::where('status', 'active')->where('due_at', '<', now())->update(['status' => 'late']);
})->hourly()->name('rentals:mark-late');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
