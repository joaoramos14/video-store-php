<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index()
    {
        $movies = Movie::with('genre')->paginate(10);
        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        $genres = Genre::all();
        return view('admin.movies.create', compact('genres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'synopsis'    => 'nullable|string',
            'year'        => 'required|integer|between:1888,' . date('Y'),
            'duration'    => 'required|integer|min:1',
            'cover'       => 'nullable|string',
            'stock'       => 'required|integer|min:0',
            'daily_price' => 'required|numeric|min:0',
            'genre_id'    => 'required|exists:genres,id',
            'tmdb_id'     => 'nullable|integer|unique:movies,tmdb_id',
        ]);

        Movie::create($data);

        return redirect()->route('admin.movies.index')->with('status', 'Filme cadastrado com sucesso!');
    }

    public function edit(Movie $movie)
    {
        $genres = Genre::all();
        return view('admin.movies.edit', compact('movie', 'genres'));
    }

    public function update(Request $request, Movie $movie)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'synopsis'    => 'nullable|string',
            'year'        => 'required|integer|between:1888,' . date('Y'),
            'duration'    => 'required|integer|min:1',
            'cover'       => 'nullable|string',
            'stock'       => 'required|integer|min:0',
            'daily_price' => 'required|numeric|min:0',
            'genre_id'    => 'required|exists:genres,id',
        ]);

        $movie->update($data);

        return redirect()->route('admin.movies.index')->with('status', 'Filme atualizado com sucesso!');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();

        return redirect()->route('admin.movies.index')->with('status', 'Filme excluído com sucesso!');
    }
}