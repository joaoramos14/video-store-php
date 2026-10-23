<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RentalException;
use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function __construct(private RentalService $rentals)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status');

        $rentals = Rental::with(['user', 'movie'])
            ->when($status === 'reserved', fn($q) => $q->where('status', Rental::RESERVED)->where('reserved_until', '>=', now()))
            ->when($status === 'active', fn($q) => $q->where('status', Rental::ACTIVE)->where('due_at', '>=', now()))
            // Igual ao isLate(): o atraso não é gravado, é calculado pelo prazo
            ->when($status === 'late', fn($q) => $q->where('status', Rental::ACTIVE)->where('due_at', '<', now()))
            ->when($status === 'returned', fn($q) => $q->where('status', Rental::RETURNED))
            ->when($status === 'cancelled', fn($q) => $q->where('status', Rental::CANCELLED))
            // Igual ao displayStatus(): reserva com prazo vencido já conta como expirada
            ->when($status === 'expired', fn($q) => $q->where(function ($q) {
                $q->where('status', Rental::EXPIRED)
                    ->orWhere(fn($q) => $q->where('status', Rental::RESERVED)->where('reserved_until', '<', now()));
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.rentals.index', compact('rentals', 'status'));
    }

    /** O cliente chegou à loja: confirma a retirada e começa a contar o prazo. */
    public function pickup(Rental $rental)
    {
        try {
            $rental = $this->rentals->pickup($rental);
        } catch (RentalException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Retirada confirmada. Devolução prevista para ' . $rental->due_at->format('d/m/Y') . '.');
    }

    public function return(Rental $rental)
    {
        try {
            $rental = $this->rentals->giveBack($rental);
        } catch (RentalException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = 'Devolução registrada com sucesso!';
        if ($rental->late_fee > 0) {
            $message .= ' Multa por atraso: R$ ' . number_format($rental->late_fee, 2, ',', '.');
        }

        return back()->with('status', $message);
    }
}
