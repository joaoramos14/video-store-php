<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('movies')->paginate(10);
        return view('admin.genres.index', compact('genres'));
    }

    public function create()
    {
        $tmdbGenres = [];

        if (config('services.tmdb.key')) {
            $response = Http::withOptions(['verify' => config('services.tmdb.verify', true)])->get("https://api.themoviedb.org/3/genre/movie/list", [
                'api_key'  => config('services.tmdb.key'),
                'language' => 'pt-BR',
            ]);

            if ($response->successful()) {
                $tmdbGenres = $response->json()['genres'] ?? [];
            }
        }

        // Pega os tmdb_ids dos gêneros já cadastrados
        $importedTmdbIds = Genre::whereNotNull('tmdb_id')->pluck('tmdb_id')->toArray();

        return view('admin.genres.create', compact('tmdbGenres', 'importedTmdbIds'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tmdb_id' => 'required|integer|unique:genres,tmdb_id',
            'name'    => 'required|string|max:255|unique:genres,name',
        ], [
            'tmdb_id.required' => 'Selecione um gênero válido da lista de sugestões.',
            'tmdb_id.unique'   => 'Este gênero do TMDB já foi cadastrado.',
            'name.unique'      => 'Já existe um gênero com este nome.',
        ]);

        Genre::create($data);

        return redirect()->route('admin.genres.index')->with('status', 'Gênero cadastrado com sucesso!');
    }

    public function edit(Genre $genre)
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:genres,name,' . $genre->id,
        ]);

        $genre->update($data);

        return redirect()->route('admin.genres.index')->with('status', 'Gênero atualizado com sucesso!');
    }

    public function destroy(Genre $genre)
    {
        if ($genre->movies()->exists()) {
            return back()->with('error', 'Não é possível excluir este gênero pois existem filmes associados a ele.');
        }

        $genre->delete();

        return redirect()->route('admin.genres.index')->with('status', 'Gênero excluído com sucesso!');
    }
}
