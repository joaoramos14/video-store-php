<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::with('genre');

        // Filtro por busca de título
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filtro por gênero
        if ($request->filled('genre_id')) {
            $query->where('genre_id', $request->genre_id);
        }

        $movies = $query->paginate(12);
        $genres = Genre::all();

        return view('movies.index', compact('movies', 'genres'));
    }

    public function show(Movie $movie)
    {
        $movie->load(['genre', 'reviews.user']);

        return view('movies.show', compact('movie'));
    }
}
