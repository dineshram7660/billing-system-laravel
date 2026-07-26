<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * `/` redirects to login (guest) or the dashboard (authenticated) —
     * there's no public marketing page on an admin panel.
     */
    public function test_root_redirects_to_login_when_guest(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
