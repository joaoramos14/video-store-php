<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Abas "Cancelados" e "Expirados" da lista de aluguéis do admin.
 */
class AdminRentalFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $title = fn(string $t) => Movie::factory()->create(['title' => $t])->id;

        Rental::factory()->reserved()->create(['movie_id' => $title('Reserva Aberta')]);
        Rental::factory()->lapsed()->create(['movie_id' => $title('Reserva Vencida')]);
        Rental::factory()->expired()->create(['movie_id' => $title('Reserva Expirada')]);
        Rental::factory()->cancelled()->create(['movie_id' => $title('Reserva Cancelada')]);
    }

    private function list(?string $status)
    {
        return $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.rentals.index', $status ? ['status' => $status] : []));
    }

    public function test_cancelled_tab_lists_only_cancelled_reservations(): void
    {
        $this->list('cancelled')->assertOk()
            ->assertSee('Reserva Cancelada')
            ->assertDontSee('Reserva Aberta')
            ->assertDontSee('Reserva Vencida')
            ->assertDontSee('Reserva Expirada');
    }

    public function test_expired_tab_includes_lapsed_reservations_not_yet_marked(): void
    {
        $this->list('expired')->assertOk()
            ->assertSee('Reserva Expirada')
            ->assertSee('Reserva Vencida')
            ->assertDontSee('Reserva Aberta')
            ->assertDontSee('Reserva Cancelada');
    }

    public function test_reserved_tab_does_not_include_lapsed_reservations(): void
    {
        $this->list('reserved')->assertOk()
            ->assertSee('Reserva Aberta')
            ->assertDontSee('Reserva Vencida');
    }

    public function test_the_new_tabs_are_shown(): void
    {
        $this->list(null)->assertOk()
            ->assertSee('Cancelados')
            ->assertSee('Expirados');
    }
}
