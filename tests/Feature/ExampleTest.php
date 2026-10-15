<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_catalog_requires_login(): void
    {
        $this->get('/')->assertStatus(401);
    }

    public function test_the_catalog_loads_for_a_logged_in_user(): void
    {
        $this->withoutVite();

        $this->actingAs(User::factory()->create())->get('/')->assertOk();
    }
}
