<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'title'    => 'nullable|string|max:255',
            'genre_id' => 'nullable|integer',
            'year'     => 'nullable|integer',
        ]);

        // with('genre') e withAvg evitam o N+1: tudo vem em poucas queries,
        // em vez de uma consulta de gênero e uma de média por filme.
        $movies = Movie::with('genre')
            ->withAvg('reviews', 'rating')
            ->when($request->title, fn($q, $t) => $q->where('title', 'like', "%{$t}%"))
            ->when($request->genre_id, fn($q, $g) => $q->where('genre_id', $g))
            ->when($request->year, fn($q, $y) => $q->where('year', $y))
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        // Opções do formulário de filtro
        $genres = Genre::orderBy('name')->get(['id', 'name']);
        $years  = Movie::query()->distinct()->orderByDesc('year')->pluck('year');

        return view('user.movies.index', compact('movies', 'genres', 'years'));
    }

    public function show(Movie $movie)
    {
        $movie->load('genre')->loadAvg('reviews', 'rating')->loadCount('reviews');

        // Quantas avaliações de cada nota (1 a 5), para o gráfico de barras
        $distribution = $movie->reviews()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $reviews = $movie->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(6)
            ->fragment('avaliacoes');

        // Aluguel em andamento do próprio usuário para este filme (se houver)
        $activeRental = Rental::where('user_id', Auth::id())
            ->where('movie_id', $movie->id)
            ->whereIn('status', ['active', 'late'])
            ->latest()
            ->first();

        return view('user.movies.show', compact('movie', 'distribution', 'reviews', 'activeRental'));
    }
}
