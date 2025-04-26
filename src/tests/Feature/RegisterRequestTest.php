<?php

namespace Tests\Feature;

use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    public function test_email_required_validation()
    {
        $request = new RegisterRequest();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => '', // メールアドレス未入力
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
    }
}
