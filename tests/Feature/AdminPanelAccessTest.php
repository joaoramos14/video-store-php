<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_panel_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_customers_cannot_open_the_admin_panel(): void
    {
        $this->actingAs($this->customer())->get('/admin')->assertForbidden();
    }

    public function test_admins_can_open_the_admin_panel(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk();
    }
}
