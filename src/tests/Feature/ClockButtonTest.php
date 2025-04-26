<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ClockButtonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    // 出勤機能
    public function test_user_can_clock_in_successfully()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/attendance/start');
        $response->assertStatus(302);
    }

    public function test_user_cannot_clock_in_twice()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/attendance/start');
        $response = $this->actingAs($user)->post('/attendance/start');
        $response->assertSessionHasErrors();
    }

    // 休憩機能
    public function test_user_can_start_break()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/attendance/break/start');
        $response->assertStatus(302);
    }

    public function test_user_can_end_break()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/attendance/break/start');
        $response = $this->actingAs($user)->post('/attendance/break/end');
        $response->assertStatus(302);
    }

    // 退勤機能
    public function test_user_can_clock_out_successfully()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/attendance/start');
        $response = $this->actingAs($user)->post('/attendance/end');
        $response->assertStatus(302);
    }

    public function test_user_cannot_clock_out_without_clocking_in()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/attendance/end');
        $response->assertSessionHasErrors();
    }

    // 勤怠一覧取得機能
    public function test_user_can_view_attendance_list()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
    }

    public function test_attendance_list_shows_correct_data()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('勤務記録');
    }
}
