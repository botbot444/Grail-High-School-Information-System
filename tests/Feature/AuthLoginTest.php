<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    public function test_login_page_shows_demo_admin_credentials(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('admin@grail.school');
        $response->assertSee('Admin@1234');
    }
}
