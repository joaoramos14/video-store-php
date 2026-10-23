<?php

namespace Tests\Feature;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cadastro de gênero pelo TMDB: pela tela de gêneros (redirect) e pela tela de filme (JSON).
 */
class GenreStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_request_creates_the_genre_and_returns_it(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.genres.store'), ['tmdb_id' => 10770, 'name' => 'Cinema TV'])
            ->assertCreated()
            ->assertJsonPath('tmdb_id', 10770)
            ->assertJsonPath('name', 'Cinema TV');

        $this->assertDatabaseHas('genres', ['tmdb_id' => 10770, 'name' => 'Cinema TV']);
    }

    public function test_sending_the_same_tmdb_id_twice_does_not_duplicate_the_genre(): void
    {
        $admin = $this->admin();
        $payload = ['tmdb_id' => 10770, 'name' => 'Cinema TV'];

        $first = $this->actingAs($admin)->postJson(route('admin.genres.store'), $payload)->assertCreated();
        $this->actingAs($admin)->postJson(route('admin.genres.store'), $payload)
            ->assertOk()
            ->assertJsonPath('id', $first->json('id'));

        $this->assertSame(1, Genre::count());
    }

    public function test_a_name_used_by_another_tmdb_id_is_refused_with_a_message(): void
    {
        Genre::factory()->create(['tmdb_id' => 1, 'name' => 'Terror']);

        $this->actingAs($this->admin())
            ->postJson(route('admin.genres.store'), ['tmdb_id' => 2, 'name' => 'Terror'])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertSame(1, Genre::count());
    }

    public function test_form_request_with_a_name_in_use_goes_back_with_an_error(): void
    {
        Genre::factory()->create(['tmdb_id' => 1, 'name' => 'Terror']);

        $this->actingAs($this->admin())
            ->from(route('admin.genres.create'))
            ->post(route('admin.genres.store'), ['tmdb_id' => 2, 'name' => 'Terror'])
            ->assertRedirect(route('admin.genres.create'))
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Genre::count());
    }

    public function test_form_request_for_an_existing_genre_does_not_claim_success(): void
    {
        Genre::factory()->create(['tmdb_id' => 28, 'name' => 'Ação']);

        $this->actingAs($this->admin())
            ->post(route('admin.genres.store'), ['tmdb_id' => 28, 'name' => 'Ação'])
            ->assertRedirect(route('admin.genres.index'))
            ->assertSessionHas('error')
            ->assertSessionMissing('status');

        $this->assertSame(1, Genre::count());
    }

    public function test_form_request_for_a_new_genre_redirects_with_success(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.genres.store'), ['tmdb_id' => 28, 'name' => 'Ação'])
            ->assertRedirect(route('admin.genres.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('genres', ['tmdb_id' => 28]);
    }

    public function test_tmdb_id_and_name_are_required(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.genres.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tmdb_id', 'name']);
    }

    public function test_customers_cannot_create_genres(): void
    {
        $this->actingAs($this->customer())
            ->postJson(route('admin.genres.store'), ['tmdb_id' => 28, 'name' => 'Ação'])
            ->assertForbidden();

        $this->assertSame(0, Genre::count());
    }
}
