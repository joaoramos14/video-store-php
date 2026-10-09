<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::with('movie')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('rentals.index', compact('rentals'));
    }

    public function store(Request $request, Movie $movie)
    {
        // Verifica se há estoque
        if ($movie->stock <= 0) {
            return back()->with('error', 'Filme indisponível no estoque no momento.');
        }

        // Valida a quantidade de dias informada
        $request->validate([
            'days' => 'required|integer|min:1|max:30',
        ]);

        $days = (int) $request->days;
        $totalPrice = $movie->daily_price * $days;

        // Cria o registro do aluguel
        Rental::create([
            'user_id'     => Auth::id(),
            'movie_id'    => $movie->id,
            'rented_at'   => Carbon::now(),
            'due_date'    => Carbon::now()->addDays($days),
            'total_price' => $totalPrice,
            'status'      => 'rented',
        ]);

        // Decrementa 1 unidade do estoque
        $movie->decrement('stock');

        return redirect()->route('rentals.index')->with('status', 'Filme alugado com sucesso!');
    }
}