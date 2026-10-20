<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TmdbController extends Controller
{
    private const API = 'https://api.themoviedb.org/3';
    private const IMG = 'https://image.tmdb.org/t/p';

    /** Cliente HTTP do TMDB, com tempo limite para a tela não ficar travada se a API não responder. */
    private function tmdb(): PendingRequest
    {
        return Http::timeout(10)->withOptions(['verify' => config('services.tmdb.verify', true)]);
    }

    /** Sem chave no .env a API sempre recusa; avisamos o motivo em vez de mostrar "nada encontrado". */
    private function missingKey(): JsonResponse
    {
        return response()->json(['message' => 'Configure a TMDB_API_KEY no .env para usar o TMDB.'], 503);
    }

    /** Traduz uma resposta de erro do TMDB para uma mensagem que a tela possa mostrar. */
    private function failure(Response $response): JsonResponse
    {
        return match ($response->status()) {
            401     => response()->json(['message' => 'O TMDB recusou a chave. Confira a TMDB_API_KEY no .env.'], 502),
            404     => response()->json(['message' => 'Filme não encontrado no TMDB.'], 404),
            default => response()->json(['message' => 'O TMDB não respondeu como esperado. Tente novamente.'], 502),
        };
    }

    /**
     * Busca filmes pelo nome e devolve uma lista enxuta em JSON.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if ($query) {
            if (!config('services.tmdb.key')) {
                return $this->missingKey();
            }

            try {
                $response = $this->tmdb()->get(self::API . '/search/movie', [
                    'api_key'  => config('services.tmdb.key'),
                    'language' => 'pt-BR',
                    'query'    => $query,
                ]);
            } catch (ConnectionException) {
                return response()->json(['message' => 'Não foi possível consultar o TMDB agora.'], 503);
            }

            if (!$response->successful()) {
                return $this->failure($response);
            }

            $results = $response->json()['results'] ?? [];
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
        if (!config('services.tmdb.key')) {
            return $this->missingKey();
        }

        try {
            $response = $this->tmdb()->get(self::API . "/movie/{$id}", [
                'api_key'            => config('services.tmdb.key'),
                'language'           => 'pt-BR',
                'append_to_response' => 'credits',
            ]);
        } catch (ConnectionException) {
            return response()->json(['message' => 'Não foi possível consultar o TMDB agora.'], 503);
        }

        if (!$response->successful()) {
            return $this->failure($response);
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
            // Na ordem do TMDB: o primeiro é o gênero principal do filme
            'genres'     => collect($m['genres'] ?? [])
                ->map(fn($genre) => [
                    'id'   => $genre['id'],
                    'name' => $genre['name'] ?? '',
                ])
                ->values()
                ->all(),
        ]);
    }
}
