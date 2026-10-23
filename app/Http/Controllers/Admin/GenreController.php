<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Services\TmdbClient;
use Illuminate\Database\UniqueConstraintViolationException;
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
        $data = $request->validate([
            'tmdb_id' => 'required|integer',
            'name'    => 'required|string|max:255',
        ], [
            'tmdb_id.required' => 'Selecione um gênero válido da lista de sugestões.',
            'name.required'    => 'O nome do gênero é obrigatório.',
        ]);

        try {
            $genre = Genre::firstOrCreate(
                ['tmdb_id' => $data['tmdb_id']],
                ['name' => $data['name']]
            );
        } catch (UniqueConstraintViolationException) {
            // O firstOrCreate já resolve a corrida pelo tmdb_id. Se a exceção chegou até aqui,
            // o que sobrou foi um nome repetido, ligado a outro ID do TMDB.
            $message = 'Já existe um gênero com este nome, ligado a outro ID do TMDB.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withInput()->withErrors(['name' => $message]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id'      => $genre->id,
                'tmdb_id' => $genre->tmdb_id,
                'name'    => $genre->name,
            ], $genre->wasRecentlyCreated ? 201 : 200);
        }

        // Cadastrar de novo um gênero que já existe não cria nada: avisamos em vez de dizer "sucesso"
        if (!$genre->wasRecentlyCreated) {
            return redirect()
                ->route('admin.genres.index')
                ->with('error', 'Este gênero do TMDB já estava cadastrado.');
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
