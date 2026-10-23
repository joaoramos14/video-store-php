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
            // As abas seguem o displayStatus(): atraso e reserva vencida são calculados pelo prazo
            ->when(in_array($status, Rental::DISPLAY_STATES, true), fn($q) => $q->whereDisplayStatus($status))
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
