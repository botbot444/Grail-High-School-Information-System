<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginCsrfProtectionTest extends TestCase
{
    public function test_login_post_without_csrf_token_is_rejected_by_the_web_middleware(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertSame(302, $response->getStatusCode());
    }
}
