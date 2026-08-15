<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @test
     */
    public function the_application_redirects_to_the_borrower_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/borrower/login');
    }
}
