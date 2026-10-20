<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Busca e detalhes do TMDB usados na tela de cadastro de filme. A API é sempre simulada.
 */
class TmdbControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.tmdb.key' => 'chave-de-teste']);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_search_without_a_key_explains_what_is_missing(): void
    {
        config(['services.tmdb.key' => null]);
        Http::fake();

        $this->actingAs($this->admin())
            ->getJson(route('admin.tmdb.search', ['query' => 'matrix']))
            ->assertStatus(503)
            ->assertJsonPath('message', fn($m) => str_contains($m, 'TMDB_API_KEY'));

        Http::assertNothingSent();
    }

    public function test_search_with_a_rejected_key_says_so(): void
    {
        Http::fake(['api.themoviedb.org/*' => Http::response(['status_code' => 7], 401)]);

        $this->actingAs($this->admin())
            ->getJson(route('admin.tmdb.search', ['query' => 'matrix']))
            ->assertStatus(502)
            ->assertJsonPath('message', fn($m) => str_contains($m, 'chave'));
    }

    public function test_search_returns_a_short_list(): void
    {
        Http::fake([
            'api.themoviedb.org/*' => Http::response(['results' => [
                ['id' => 603, 'title' => 'Matrix', 'release_date' => '1999-03-30', 'poster_path' => '/m.jpg'],
                ['id' => 604, 'title' => 'Sem pôster', 'release_date' => ''],
            ]]),
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('admin.tmdb.search', ['query' => 'matrix']))
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', 603)
            ->assertJsonPath('0.year', '1999')
            ->assertJsonPath('1.poster', null)
            ->assertJsonPath('1.year', null);
    }

    public function test_details_list_genres_with_id_and_name_in_tmdb_order(): void
    {
        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'id'           => 603,
                'title'        => 'Matrix',
                'release_date' => '1999-03-30',
                'runtime'      => 136,
                'overview'     => 'Sinopse',
                'poster_path'  => '/matrix.jpg',
                'genres'       => [
                    ['id' => 28, 'name' => 'Ação'],
                    ['id' => 878, 'name' => 'Ficção científica'],
                ],
                'credits'      => ['crew' => [['job' => 'Director', 'name' => 'Lana Wachowski']]],
            ]),
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('admin.tmdb.show', ['id' => 603]))
            ->assertOk()
            ->assertJsonCount(2, 'genres')
            ->assertJsonPath('genres.0.id', 28)
            ->assertJsonPath('genres.0.name', 'Ação')
            ->assertJsonPath('genres.1.id', 878)
            ->assertJsonPath('director', 'Lana Wachowski')
            ->assertJsonMissingPath('genre_tmdb_ids');
    }

    public function test_details_of_an_unknown_movie_return_404(): void
    {
        Http::fake(['api.themoviedb.org/*' => Http::response(['status_code' => 34], 404)]);

        $this->actingAs($this->admin())
            ->getJson(route('admin.tmdb.show', ['id' => 1]))
            ->assertNotFound()
            ->assertJsonPath('message', 'Filme não encontrado no TMDB.');
    }

    public function test_details_without_a_key_explain_what_is_missing(): void
    {
        config(['services.tmdb.key' => null]);
        Http::fake();

        $this->actingAs($this->admin())
            ->getJson(route('admin.tmdb.show', ['id' => 603]))
            ->assertStatus(503);

        Http::assertNothingSent();
    }

    public function test_customers_cannot_use_the_tmdb_routes(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->getJson(route('admin.tmdb.search', ['query' => 'matrix']))
            ->assertForbidden();
    }
}
