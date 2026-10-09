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

        $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'user_id'  => Auth::id(),
            'movie_id' => $rental->movie_id,
            'rating'   => $request->rating,
            'comment'  => $request->comment,
        ]);

        return back()->with('status', 'Avaliação enviada com sucesso!');
    }
}