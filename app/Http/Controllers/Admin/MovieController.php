<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /** Regras comuns ao cadastro e à edição. */
    private function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'director'    => 'nullable|string|max:255',
            'synopsis'    => 'nullable|string',
            'year'        => 'required|integer|between:1888,' . date('Y'),
            'duration'    => 'required|integer|min:1',
            'cover'       => 'nullable|url|max:255',
            'stock'       => 'required|integer|min:0',
            'daily_price' => 'required|numeric|min:0',
            'genre_id'    => 'required|exists:genres,id',
        ];
    }

    public function index()
    {
        $movies = Movie::with('genre')->orderBy('title')->paginate(10);
        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        $genres = Genre::orderBy('name')->get();
        return view('admin.movies.create', compact('genres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            ...$this->rules(),
            // O tmdb_id só é definido no cadastro, pela busca no TMDB
            'tmdb_id' => 'nullable|integer|unique:movies,tmdb_id',
        ]);

        Movie::create($data);

        return redirect()->route('admin.movies.index')->with('status', 'Filme cadastrado com sucesso!');
    }

    public function edit(Movie $movie)
    {
        $genres = Genre::orderBy('name')->get();
        return view('admin.movies.edit', compact('movie', 'genres'));
    }

    public function update(Request $request, Movie $movie)
    {
        $data = $request->validate($this->rules());

        $movie->update($data);

        return redirect()->route('admin.movies.index')->with('status', 'Filme atualizado com sucesso!');
    }

    public function destroy(Movie $movie)
    {
        // rentals.movie_id é restrictOnDelete: sem essa checagem o MySQL devolve erro de FK (500)
        if ($movie->rentals()->exists()) {
            return back()->with('error', 'Não é possível excluir este filme pois existem aluguéis associados a ele.');
        }

        $movie->delete();

        return redirect()->route('admin.movies.index')->with('status', 'Filme excluído com sucesso!');
    }
}
