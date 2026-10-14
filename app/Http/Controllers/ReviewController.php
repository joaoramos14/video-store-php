<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Rental $rental)
    {
        // Garante que o aluguel pertence ao usuário logado
        if ($rental->user_id !== Auth::id()) {
            abort(403);
        }

        // Cada aluguel recebe uma única avaliação
        if ($rental->review()->exists()) {
            return back()->with('error', 'Este aluguel já foi avaliado.');
        }

        // Os erros ficam em um "bag" por aluguel, para aparecerem só no formulário certo
        $data = $request->validateWithBag('review_' . $rental->id, [
            'rating'  => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'rental_id' => $rental->id,
            'user_id'   => Auth::id(),
            'movie_id'  => $rental->movie_id,
            'rating'    => $data['rating'],
            'comment'   => $data['comment'] ?? null,
        ]);

        return back()->with('status', 'Avaliação enviada com sucesso!');
    }
}
