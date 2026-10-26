<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\PriceTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * O admin escolhe a faixa de preço no cadastro; a diária do filme vem da faixa.
 */
class MoviePriceTierTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'    => 'Matrix',
            'year'     => 1999,
            'duration' => 136,
            'stock'    => 2,
            'genre_id' => Genre::factory()->create()->id,
        ], $overrides);
    }

    public function test_admin_stores_a_movie_with_a_price_tier(): void
    {
        $tier = PriceTier::factory()->create(['daily_price' => 7]);

        $this->actingAs($this->admin())
            ->post(route('admin.movies.store'), $this->payload(['price_tier_id' => $tier->id]))
            ->assertRedirect(route('admin.movies.index'));

        $this->assertEquals('7.00', Movie::firstOrFail()->priceTier->daily_price);
    }

    public function test_price_tier_must_exist(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.movies.store'), $this->payload(['price_tier_id' => 999]))
            ->assertSessionHasErrors('price_tier_id');

        $this->assertDatabaseCount('movies', 0);
    }

    public function test_default_tiers_are_created_by_the_migration(): void
    {
        $this->assertEqualsCanonicalizing(['Lançamento', 'Catálogo', 'Clássico'], PriceTier::pluck('name')->all());
    }
}
