<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Services\TmdbClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('movies')->paginate(10);
        return view('admin.genres.index', compact('genres'));
    }

    public function create(TmdbClient $tmdb)
    {
        $tmdbGenres = [];

        if ($tmdb->hasKey()) {
            try {
                $response = $tmdb->get('/genre/movie/list');

                if ($response->successful()) {
                    $tmdbGenres = $response->json()['genres'] ?? [];
                }
            } catch (ConnectionException) {
                // Sem rede ou TMDB fora do ar: a tela abre normalmente, só sem as sugestões
                session()->now('error', 'Não foi possível consultar o TMDB agora. Tente novamente em instantes.');
            }
        }

        // Pega os tmdb_ids dos gêneros já cadastrados
        $importedTmdbIds = Genre::whereNotNull('tmdb_id')->pluck('tmdb_id')->toArray();

        return view('admin.genres.create', compact('tmdbGenres', 'importedTmdbIds'));
    }

    public function store(Request $request)
    {
        // Se falhar, o Laravel volta para o formulário com os erros
        // (ou responde 422 com {message, errors} quando a tela de filme chama via fetch)
        $data = $request->validate([
            'tmdb_id' => 'required|integer|unique:genres,tmdb_id',
            'name'    => 'required|string|max:255|unique:genres,name',
        ], [
            'tmdb_id.required' => 'Selecione um gênero válido da lista de sugestões.',
            'tmdb_id.unique'   => 'Este gênero do TMDB já está cadastrado.',
            'name.required'    => 'O nome do gênero é obrigatório.',
            'name.unique'      => 'Já existe um gênero com este nome.',
        ]);

        $genre = Genre::create($data);

        // A tela de cadastro de filme chama esta rota via fetch e espera JSON
        if ($request->expectsJson()) {
            return response()->json([
                'id'      => $genre->id,
                'tmdb_id' => $genre->tmdb_id,
                'name'    => $genre->name,
            ], 201);
        }

        return redirect()
            ->route('admin.genres.index')
            ->with('status', 'Gênero cadastrado com sucesso!');
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
