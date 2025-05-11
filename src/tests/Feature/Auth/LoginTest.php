<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function Login_Email_Required()
    {
        $response = $this->post('/login', [
            'password' => 'password123',
            'role' => '1',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function Login_Password_Required()
    {
        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'role' => '1',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function Login_Database_error()
    {
        $response = $this->post('/login', [
            'email' => 'incorrect@example.com',
            'password' => 'errorpassword',
            'role' => '1',
        ]);

        $response->assertSessionHasErrors('email');
    }
}