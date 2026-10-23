<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Acesso à API do TMDB: chave, idioma, tempo limite e SSL ficam só aqui.
 * Lança ConnectionException se a API não responder; quem chama decide o que mostrar.
 */
class TmdbClient
{
    private const API = 'https://api.themoviedb.org/3';

    /** Sem chave no .env a API sempre recusa. */
    public function hasKey(): bool
    {
        return (bool) config('services.tmdb.key');
    }

    public function get(string $path, array $query = []): Response
    {
        // Tempo limite para a tela não ficar travada se a API não responder
        return Http::timeout(10)
            ->withOptions(['verify' => config('services.tmdb.verify', true)])
            ->get(self::API . $path, [
                'api_key'  => config('services.tmdb.key'),
                'language' => 'pt-BR',
                ...$query,
            ]);
    }
}
