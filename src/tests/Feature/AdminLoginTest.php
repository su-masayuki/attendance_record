<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_email_required_validation()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'adminpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_admin_login_password_required_validation()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
