<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_catalog_requires_login(): void
    {
        $this->get('/')->assertStatus(401);
    }

    public function test_the_catalog_loads_for_a_logged_in_user(): void
    {
        $this->actingAs($this->customer())->get('/')->assertOk();
    }
}
