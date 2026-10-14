<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TmdbController extends Controller
{
    private const API = 'https://api.themoviedb.org/3';
    private const IMG = 'https://image.tmdb.org/t/p';

    /**
     * Busca filmes pelo nome e devolve uma lista enxuta em JSON.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if ($query) {
            $response = Http::withOptions(['verify' => config('services.tmdb.verify', true)])->get(self::API . '/search/movie', [
                'api_key'  => config('services.tmdb.key'),
                'language' => 'pt-BR',
                'query'    => $query,
            ]);

            if ($response->successful()) {
                $results = $response->json()['results'] ?? [];
            }
        }

        // Só a tela de cadastro usa esta rota (via fetch), então sempre respondemos JSON.
        return response()->json(
            collect($results)->take(8)->map(fn($m) => [
                'id'     => $m['id'],
                'title'  => $m['title'] ?? '',
                'year'   => substr($m['release_date'] ?? '', 0, 4) ?: null,
                'poster' => !empty($m['poster_path']) ? self::IMG . '/w92' . $m['poster_path'] : null,
            ])->values()
        );
    }

    /**
     * Detalhes de um filme já no formato das colunas da tabela movies,
     * incluindo diretor e duração, que a busca não traz.
     */
    public function show(int $id)
    {
        $response = Http::withOptions(['verify' => config('services.tmdb.verify', true)])->get(self::API . "/movie/{$id}", [
            'api_key'            => config('services.tmdb.key'),
            'language'           => 'pt-BR',
            'append_to_response' => 'credits',
        ]);

        if (!$response->successful()) {
            return response()->json(['message' => 'Filme não encontrado no TMDB.'], 404);
        }

        $m = $response->json();
        $director = collect($m['credits']['crew'] ?? [])->firstWhere('job', 'Director')['name'] ?? null;

        return response()->json([
            'tmdb_id'    => $m['id'],
            'title'      => $m['title'] ?? '',
            'director'   => $director,
            'synopsis'   => $m['overview'] ?? '',
            'year'       => (int) substr($m['release_date'] ?? '', 0, 4) ?: null,
            'duration'   => $m['runtime'] ?? null,
            'cover'      => !empty($m['poster_path']) ? self::IMG . '/w500' . $m['poster_path'] : null,
            'genre_tmdb_ids' => collect($m['genres'] ?? [])->pluck('id')->all(),
        ]);
    }
}
