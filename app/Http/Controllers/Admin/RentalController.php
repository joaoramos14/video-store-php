<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::with(['user', 'movie'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.rentals.index', compact('rentals'));
    }

    public function return(Rental $rental)
    {
        if ($rental->status === 'returned') {
            return back()->with('error', 'Este filme já foi devolvido.');
        }

        $now = Carbon::now();
        $lateFee = 0;

        // Se a devolução for feita após a data prevista, calcula a multa
        if ($now->greaterThan($rental->due_date)) {
            $daysLate = $now->diffInDays($rental->due_date);
            // Multa: preço diário do filme * dias de atraso * 1.5
            $lateFee = $daysLate * $rental->movie->daily_price * 1.5;
        }

        $rental->update([
            'returned_at' => $now,
            'late_fee'    => $lateFee,
            'status'      => 'returned',
        ]);

        // Devolve 1 unidade ao estoque do filme
        $rental->movie->increment('stock');

        $message = 'Devolução registrada com sucesso!';
        if ($lateFee > 0) {
            $message .= ' Multa por atraso: R$ ' . number_format($lateFee, 2, ',', '.');
        }

        return back()->with('status', $message);
    }
}
