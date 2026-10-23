<?php

namespace App\Http\Controllers;

use App\Exceptions\RentalException;
use App\Models\Movie;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalController extends Controller
{
    public function __construct(private RentalService $rentals)
    {
    }

    public function index()
    {
        $rentals = Rental::with(['movie', 'review'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.rentals.index', compact('rentals'));
    }

    /** Reserva o filme pelo site. A retirada e o início do prazo acontecem na loja. */
    public function store(Request $request, Movie $movie)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:30',
        ]);

        try {
            $this->rentals->reserve(Auth::user(), $movie, (int) $request->days);
        } catch (RentalException $e) {
            return back()->with('error', $e->getMessage());
        }

        $hours = config('rentals.reservation_hours');

        return redirect()->route('rentals.index')
            ->with('status', "Reserva feita! Retire o filme na loja em até {$hours} horas.");
    }

    /** O cliente desiste da reserva. */
    public function cancel(Rental $rental)
    {
        try {
            $this->rentals->cancel($rental);
        } catch (RentalException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Reserva cancelada.');
    }
}
