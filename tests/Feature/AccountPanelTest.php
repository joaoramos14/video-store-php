<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Auth\Pages\Register;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_account_login(): void
    {
        $this->get('/my-rentals')->assertRedirect('/account/login');
    }

    public function test_login_and_register_pages_load(): void
    {
        $this->get('/account/login')->assertOk();
        $this->get('/account/register')->assertOk();
    }

    public function test_a_visitor_can_create_an_account(): void
    {
        Filament::setCurrentPanel('account');

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Maria Silva',
                'email' => 'maria@example.com',
                'password' => 'senha-segura-123',
                'passwordConfirmation' => 'senha-segura-123',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'maria@example.com')->firstOrFail();

        // quem se cadastra pelo site nunca vira admin
        $this->assertFalse($user->isAdmin());
        $this->assertAuthenticatedAs($user);
    }

    public function test_the_account_root_sends_logged_in_users_to_the_catalog(): void
    {
        // Protege contra o loop de redirecionamento: sem a rota home, /account redirecionava para ele mesmo
        $this->actingAs($this->customer())
            ->get('/account')
            ->assertRedirect(route('movies.index'));
    }

    public function test_users_can_log_out(): void
    {
        $this->actingAs($this->customer())
            ->post(route('filament.account.auth.logout'))
            ->assertRedirect('/account/login');

        $this->assertGuest();
    }

    public function test_customers_see_the_logout_button_but_not_the_admin_link(): void
    {
        $this->actingAs($this->customer())
            ->get('/my-rentals')
            ->assertSee('Sair')
            ->assertDontSee('Painel admin');
    }

    public function test_admins_see_the_admin_panel_link(): void
    {
        $this->actingAs($this->admin())
            ->get('/my-rentals')
            ->assertSee('Painel admin');
    }
}
