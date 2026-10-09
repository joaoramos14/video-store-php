<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TmdbController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if ($query) {
            $response = Http::get("https://api.themoviedb.org/3/search/movie", [
                'api_key'  => config('services.tmdb.key'),
                'language' => 'pt-BR',
                'query'    => $query,
            ]);

            if ($response->successful()) {
                $results = $response->json()['results'] ?? [];
            }
        }

        return view('admin.tmdb.search', compact('results', 'query'));
    }
}
