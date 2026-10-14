<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::with(['movie', 'review'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.rentals.index', compact('rentals'));
    }

    public function store(Request $request, Movie $movie)
    {
        // Valida a quantidade de dias informada
        $request->validate([
            'days' => 'required|integer|min:1|max:30',
        ]);

        $days = (int) $request->days;

        // A transação com lock evita que dois clientes aluguem a última cópia ao mesmo tempo
        $rented = DB::transaction(function () use ($movie, $days) {
            $locked = Movie::lockForUpdate()->find($movie->id);

            if ($locked->stock <= 0) {
                return 'stock';
            }

            // Impede alugar de novo um filme que o usuário ainda não devolveu
            $alreadyRenting = Rental::where('user_id', Auth::id())
                ->where('movie_id', $locked->id)
                ->whereIn('status', ['active', 'late'])
                ->exists();

            if ($alreadyRenting) {
                return 'duplicate';
            }

            $now = Carbon::now();

            Rental::create([
                'user_id'     => Auth::id(),
                'movie_id'    => $locked->id,
                'rented_at'   => $now,
                'due_at'      => $now->copy()->addDays($days),
                'days'        => $days,
                'total_price' => $locked->daily_price * $days,
                'status'      => 'active',
            ]);

            // Retira 1 unidade do estoque
            $locked->decrement('stock');

            return 'ok';
        });

        if ($rented === 'stock') {
            return back()->with('error', 'Filme indisponível no estoque no momento.');
        }

        if ($rented === 'duplicate') {
            return back()->with('error', 'Você já está com este filme alugado. Devolva-o antes de alugar novamente.');
        }

        return redirect()->route('rentals.index')->with('status', 'Filme alugado com sucesso!');
    }
}
