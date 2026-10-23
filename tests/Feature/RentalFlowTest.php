<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fluxo: reservar (site) -> confirmar retirada (balcão) -> devolver (balcão).
 * Desvios: cancelar (cliente) e expirar (ninguém retirou no prazo).
 */
class RentalFlowTest extends TestCase
{
    use RefreshDatabase;

    private const START = '2026-10-14 10:00:00';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();   // as telas usam @vite e os assets podem não estar compilados
        Carbon::setTestNow(self::START);
    }

    private function movie(int $stock = 2, float $price = 10): Movie
    {
        return Movie::factory()->create(['stock' => $stock, 'daily_price' => $price]);
    }

    private function customer(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function reserve(User $user, Movie $movie, int $days = 3)
    {
        return $this->actingAs($user)->post(route('rentals.store', $movie), ['days' => $days]);
    }

    // ---------------------------------------------------------------- reserva

    public function test_reserving_creates_a_reservation_and_takes_one_copy(): void
    {
        $user = $this->customer();
        $movie = $this->movie(stock: 3, price: 10);

        $this->reserve($user, $movie, 3)->assertRedirect(route('rentals.index'));

        $rental = Rental::firstOrFail();
        $this->assertSame(Rental::RESERVED, $rental->status);
        $this->assertNull($rental->rented_at);
        $this->assertNull($rental->due_at);
        $this->assertSame(3, $rental->days);
        $this->assertEquals('30.00', $rental->total_price);
        $this->assertTrue($rental->reserved_until->equalTo(now()->addHours(24)));
        $this->assertSame(2, $movie->fresh()->stock);
    }

    public function test_cannot_reserve_without_stock(): void
    {
        $this->reserve($this->customer(), $this->movie(stock: 0))
            ->assertSessionHas('error');

        $this->assertSame(0, Rental::count());
    }

    public function test_days_must_be_between_1_and_30(): void
    {
        $user = $this->customer();
        $movie = $this->movie();

        $this->reserve($user, $movie, 0)->assertSessionHasErrors('days');
        $this->reserve($user, $movie, 31)->assertSessionHasErrors('days');

        $this->assertSame(0, Rental::count());
        $this->assertSame(2, $movie->fresh()->stock);
    }

    public function test_cannot_reserve_the_same_movie_twice(): void
    {
        $user = $this->customer();
        $movie = $this->movie(stock: 5);

        $this->reserve($user, $movie);
        $this->reserve($user, $movie)->assertSessionHas('error');

        $this->assertSame(1, Rental::count());
        $this->assertSame(4, $movie->fresh()->stock);
    }

    public function test_cannot_reserve_again_while_the_movie_is_still_with_the_customer(): void
    {
        $user = $this->customer();
        $movie = $this->movie(stock: 5);

        $this->reserve($user, $movie);
        $this->actingAs($this->admin())->post(route('admin.rentals.pickup', Rental::first()));

        $this->reserve($user, $movie)->assertSessionHas('error');
        $this->assertSame(1, Rental::count());
    }

    public function test_can_reserve_again_after_returning_the_movie(): void
    {
        $user = $this->customer();
        $movie = $this->movie(stock: 1);
        $admin = $this->admin();

        $this->reserve($user, $movie);
        $this->actingAs($admin)->post(route('admin.rentals.pickup', Rental::first()));
        $this->actingAs($admin)->post(route('admin.rentals.return', Rental::first()));

        $this->reserve($user, $movie)->assertSessionMissing('error');
        $this->assertSame(2, Rental::count());
    }

    // --------------------------------------------------------------- cancelar

    public function test_cancelling_returns_the_copy_to_stock(): void
    {
        $user = $this->customer();
        $movie = $this->movie(stock: 2);
        $this->reserve($user, $movie);

        $this->actingAs($user)->post(route('rentals.cancel', Rental::first()))
            ->assertSessionHas('status');

        $this->assertSame(Rental::CANCELLED, Rental::first()->status);
        $this->assertSame(2, $movie->fresh()->stock);
    }

    public function test_cannot_cancel_someone_elses_reservation(): void
    {
        $movie = $this->movie();
        $this->reserve($this->customer(), $movie);

        $this->actingAs($this->customer())
            ->post(route('rentals.cancel', Rental::first()))
            ->assertForbidden();

        $this->assertSame(Rental::RESERVED, Rental::first()->status);
    }

    public function test_cannot_cancel_after_pickup(): void
    {
        $user = $this->customer();
        $movie = $this->movie();
        $this->reserve($user, $movie);
        $this->actingAs($this->admin())->post(route('admin.rentals.pickup', Rental::first()));

        $this->actingAs($user)->post(route('rentals.cancel', Rental::first()))
            ->assertSessionHas('error');

        $this->assertSame(Rental::ACTIVE, Rental::first()->status);
        $this->assertSame(1, $movie->fresh()->stock);
    }

    // --------------------------------------------------------------- expirar

    public function test_scheduled_command_expires_only_lapsed_reservations(): void
    {
        $movie = $this->movie(stock: 5);
        $this->reserve($this->customer(), $movie);          // vence em 15/10 10:00

        $this->travelTo(Carbon::parse(self::START)->addHours(20));
        $this->reserve($this->customer(), $movie);          // vence em 16/10 06:00

        $this->travelTo(Carbon::parse(self::START)->addHours(25));
        $this->artisan('rentals:expire-reservations')->assertSuccessful();

        $first = Rental::orderBy('id')->first();
        $second = Rental::orderBy('id')->skip(1)->first();

        $this->assertSame(Rental::EXPIRED, $first->status);
        $this->assertSame(Rental::RESERVED, $second->status);
        $this->assertSame(4, $movie->fresh()->stock);       // 5 - 2 reservas + 1 devolvida
    }

    public function test_a_lapsed_reservation_frees_the_copy_for_the_next_customer(): void
    {
        $movie = $this->movie(stock: 1);
        $this->reserve($this->customer(), $movie);
        $this->assertSame(0, $movie->fresh()->stock);

        // Passou do prazo e a tarefa agendada ainda não rodou
        $this->travelTo(Carbon::parse(self::START)->addHours(25));

        $this->reserve($this->customer(), $movie)->assertSessionMissing('error');

        $this->assertSame(Rental::EXPIRED, Rental::orderBy('id')->first()->status);
        $this->assertSame(Rental::RESERVED, Rental::orderBy('id')->skip(1)->first()->status);
        $this->assertSame(0, $movie->fresh()->stock);
    }

    public function test_display_status_shows_expired_as_soon_as_the_deadline_passes(): void
    {
        $this->reserve($this->customer(), $this->movie());
        $rental = Rental::first();

        $this->assertSame('reserved', $rental->displayStatus());

        $this->travelTo(Carbon::parse(self::START)->addHours(25));
        $this->assertSame('expired', $rental->fresh()->displayStatus());
    }

    // --------------------------------------------------------------- retirada

    public function test_pickup_starts_the_rental_clock(): void
    {
        $movie = $this->movie(stock: 2);
        $this->reserve($this->customer(), $movie, 3);

        $this->travelTo(Carbon::parse(self::START)->addHours(2));
        $this->actingAs($this->admin())->post(route('admin.rentals.pickup', Rental::first()))
            ->assertSessionHas('status');

        $rental = Rental::first();
        $this->assertSame(Rental::ACTIVE, $rental->status);
        $this->assertTrue($rental->rented_at->equalTo(now()));
        $this->assertTrue($rental->due_at->equalTo(now()->addDays(3)));
        $this->assertSame(1, $movie->fresh()->stock);       // a cópia já estava separada pela reserva
    }

    public function test_only_admins_can_confirm_pickup(): void
    {
        $user = $this->customer();
        $this->reserve($user, $this->movie());

        $this->actingAs($user)->post(route('admin.rentals.pickup', Rental::first()))->assertForbidden();

        $this->assertSame(Rental::RESERVED, Rental::first()->status);
    }

    public function test_pickup_after_the_deadline_is_refused_and_returns_the_copy(): void
    {
        $movie = $this->movie(stock: 1);
        $this->reserve($this->customer(), $movie);

        $this->travelTo(Carbon::parse(self::START)->addHours(25));
        $this->actingAs($this->admin())->post(route('admin.rentals.pickup', Rental::first()))
            ->assertSessionHas('error');

        $this->assertSame(Rental::EXPIRED, Rental::first()->status);
        $this->assertSame(1, $movie->fresh()->stock);
    }

    public function test_pickup_cannot_happen_twice(): void
    {
        $this->reserve($this->customer(), $this->movie());
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.rentals.pickup', Rental::first()));
        $dueAt = Rental::first()->due_at;

        $this->travelTo(now()->addDay());
        $this->actingAs($admin)->post(route('admin.rentals.pickup', Rental::first()))
            ->assertSessionHas('error');

        $this->assertTrue(Rental::first()->due_at->equalTo($dueAt));
    }

    // -------------------------------------------------------------- devolução

    public function test_return_on_time_has_no_late_fee_and_restores_stock(): void
    {
        $movie = $this->movie(stock: 2, price: 10);
        $admin = $this->admin();
        $this->reserve($this->customer(), $movie, 3);
        $this->actingAs($admin)->post(route('admin.rentals.pickup', Rental::first()));

        $this->travelTo(now()->addDays(2));
        $this->actingAs($admin)->post(route('admin.rentals.return', Rental::first()));

        $rental = Rental::first();
        $this->assertSame(Rental::RETURNED, $rental->status);
        $this->assertEquals('0.00', $rental->late_fee);
        $this->assertNotNull($rental->returned_at);
        $this->assertSame(2, $movie->fresh()->stock);
    }

    public function test_late_return_charges_daily_price_times_days_late_times_1_5(): void
    {
        $movie = $this->movie(stock: 1, price: 10);
        $admin = $this->admin();
        $this->reserve($this->customer(), $movie, 3);                  // total 30,00
        $this->actingAs($admin)->post(route('admin.rentals.pickup', Rental::first()));

        $this->travelTo(now()->addDays(3)->addDays(2));                // 2 dias depois do prazo
        $this->actingAs($admin)->post(route('admin.rentals.return', Rental::first()));

        $this->assertEquals('30.00', Rental::first()->late_fee);       // 2 * 10 * 1,5
    }

    public function test_a_fraction_of_a_day_late_counts_as_a_full_day(): void
    {
        $movie = $this->movie(stock: 1, price: 10);
        $admin = $this->admin();
        $this->reserve($this->customer(), $movie, 1);
        $this->actingAs($admin)->post(route('admin.rentals.pickup', Rental::first()));

        $this->travelTo(now()->addDay()->addHour());                   // 1 hora depois do prazo
        $this->actingAs($admin)->post(route('admin.rentals.return', Rental::first()));

        $this->assertEquals('15.00', Rental::first()->late_fee);       // 1 * 10 * 1,5
    }

    public function test_overdue_rental_shows_as_late_without_changing_the_stored_status(): void
    {
        $movie = $this->movie(stock: 1);
        $this->reserve($this->customer(), $movie, 1);
        $this->actingAs($this->admin())->post(route('admin.rentals.pickup', Rental::first()));

        $this->travelTo(now()->addDays(2));

        $rental = Rental::first();
        $this->assertSame(Rental::ACTIVE, $rental->status);
        $this->assertTrue($rental->isLate());
        $this->assertSame(Rental::LATE, $rental->displayStatus());
    }

    public function test_cannot_return_a_movie_that_was_not_picked_up(): void
    {
        $movie = $this->movie(stock: 2);
        $this->reserve($this->customer(), $movie);

        $this->actingAs($this->admin())->post(route('admin.rentals.return', Rental::first()))
            ->assertSessionHas('error');

        $this->assertSame(Rental::RESERVED, Rental::first()->status);
        $this->assertSame(1, $movie->fresh()->stock);
    }

    public function test_cannot_return_twice(): void
    {
        $movie = $this->movie(stock: 1);
        $admin = $this->admin();
        $this->reserve($this->customer(), $movie);
        $this->actingAs($admin)->post(route('admin.rentals.pickup', Rental::first()));
        $this->actingAs($admin)->post(route('admin.rentals.return', Rental::first()));

        $this->actingAs($admin)->post(route('admin.rentals.return', Rental::first()))
            ->assertSessionHas('error');

        $this->assertSame(1, $movie->fresh()->stock);
    }

    // -------------------------------------------------------------- avaliação

    public function test_review_is_refused_until_the_movie_is_picked_up(): void
    {
        $user = $this->customer();
        $this->reserve($user, $this->movie());

        $this->actingAs($user)->post(route('reviews.store', Rental::first()), ['rating' => 5])
            ->assertSessionHas('error');

        $this->assertSame(0, \App\Models\Review::count());
    }

    public function test_cannot_review_someone_elses_rental(): void
    {
        $this->reserve($this->customer(), $this->movie());
        $this->actingAs($this->admin())->post(route('admin.rentals.pickup', Rental::first()));

        $this->actingAs($this->customer())
            ->post(route('reviews.store', Rental::first()), ['rating' => 5])
            ->assertForbidden();

        $this->assertSame(0, \App\Models\Review::count());
    }

    public function test_review_is_accepted_once_after_pickup(): void
    {
        $user = $this->customer();
        $this->reserve($user, $this->movie());
        $this->actingAs($this->admin())->post(route('admin.rentals.pickup', Rental::first()));

        $this->actingAs($user)->post(route('reviews.store', Rental::first()), ['rating' => 4, 'comment' => 'Bom'])
            ->assertSessionHas('status');
        $this->actingAs($user)->post(route('reviews.store', Rental::first()), ['rating' => 1])
            ->assertSessionHas('error');

        $this->assertSame(1, \App\Models\Review::count());
        $this->assertSame(4, \App\Models\Review::first()->rating);
    }

    // ------------------------------------------------------------------ telas

    public function test_movie_page_replaces_the_form_with_a_notice_when_reserved(): void
    {
        $user = $this->customer();
        $movie = $this->movie();
        $this->reserve($user, $movie);

        $this->actingAs($user)->get(route('movies.show', $movie))
            ->assertOk()
            ->assertSee('Você reservou este filme')
            ->assertDontSee('name="days"', false);
    }

    public function test_my_rentals_page_offers_cancel_for_reservations(): void
    {
        $user = $this->customer();
        $this->reserve($user, $this->movie());

        $this->actingAs($user)->get(route('rentals.index'))
            ->assertOk()
            ->assertSee('Cancelar reserva');
    }

    public function test_admin_rentals_page_offers_pickup_for_reservations(): void
    {
        $this->reserve($this->customer(), $this->movie());

        $this->actingAs($this->admin())->get(route('admin.rentals.index', ['status' => 'reserved']))
            ->assertOk()
            ->assertSee('Confirmar retirada');
    }
}
