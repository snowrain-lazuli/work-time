<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function AdminLogin_Email_Required()
    {
        $response = $this->post('/login', [
            'password' => 'password123',
            'role' => '2',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function AdminLogin_Password_Required()
    {
        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'role' => '2',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function AdminLogin_Database_error()
    {
        $response = $this->post('/login', [
            'email' => 'incorrect@example.com',
            'password' => 'errorpassword',
            'role' => '2',
        ]);

        $response->assertSessionHasErrors('email');
    }
}