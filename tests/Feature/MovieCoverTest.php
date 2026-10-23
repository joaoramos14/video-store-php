<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Capa do filme: só URL completa (a busca no TMDB grava a URL /w500).
 */
class MovieCoverTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'       => 'Matrix',
            'year'        => 1999,
            'duration'    => 136,
            'stock'       => 2,
            'daily_price' => 5,
            'genre_id'    => Genre::factory()->create()->id,
        ], $overrides);
    }

    public function test_cover_url_returns_full_urls_only(): void
    {
        $this->assertSame('https://image.tmdb.org/t/p/w500/m.jpg', Movie::factory()->make(['cover' => 'https://image.tmdb.org/t/p/w500/m.jpg'])->cover_url);
        $this->assertNull(Movie::factory()->make(['cover' => '/m.jpg'])->cover_url);
        $this->assertNull(Movie::factory()->make(['cover' => null])->cover_url);
    }

    public function test_cover_must_be_a_url(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.movies.store'), $this->payload(['cover' => '/m.jpg']))
            ->assertSessionHasErrors(['cover' => 'O campo capa deve ser uma URL válida.']);

        $this->assertDatabaseCount('movies', 0);
    }
}
