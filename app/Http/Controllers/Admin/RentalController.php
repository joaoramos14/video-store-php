<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $rentals = Rental::with(['user', 'movie'])
            ->when($status === 'active', fn($q) => $q->where('status', 'active')->where('due_at', '>=', now()))
            ->when($status === 'late', fn($q) => $q->where(function ($q) {
                $q->where('status', 'late')
                    ->orWhere(fn($q) => $q->where('status', 'active')->where('due_at', '<', now()));
            }))
            ->when($status === 'returned', fn($q) => $q->where('status', 'returned'))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.rentals.index', compact('rentals', 'status'));
    }

    public function return(Rental $rental)
    {
        if ($rental->status === 'returned') {
            return back()->with('error', 'Este filme já foi devolvido.');
        }

        $now = Carbon::now();
        $lateFee = 0;

        // Se a devolução for feita após o prazo, calcula a multa
        if ($now->greaterThan($rental->due_at)) {
            // Cada dia (ou fração) de atraso conta como um dia
            $daysLate = (int) ceil($rental->due_at->diffInSeconds($now) / 86400);
            // Multa: diária paga no aluguel * dias de atraso * 1.5
            $dailyPrice = $rental->total_price / max($rental->days, 1);
            $lateFee = round($daysLate * $dailyPrice * 1.5, 2);
        }

        DB::transaction(function () use ($rental, $now, $lateFee) {
            $rental->update([
                'returned_at' => $now,
                'late_fee'    => $lateFee,
                'status'      => 'returned',
            ]);

            // Devolve 1 unidade ao estoque do filme
            $rental->movie->increment('stock');
        });

        $message = 'Devolução registrada com sucesso!';
        if ($lateFee > 0) {
            $message .= ' Multa por atraso: R$ ' . number_format($lateFee, 2, ',', '.');
        }

        return back()->with('status', $message);
    }
}
