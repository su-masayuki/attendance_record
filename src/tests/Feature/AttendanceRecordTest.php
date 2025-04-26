<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceRecordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registration_name_required_validation()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function registration_email_required_validation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function registration_password_min_validation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function registration_password_confirmation_validation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function registration_password_required_validation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function registration_successful()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'success@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/attendance');
        $this->assertDatabaseHas('users', [
            'email' => 'success@example.com',
        ]);
    }

    /** @test */
    public function current_datetime_format_is_correct()
    {
        Carbon::setTestNow(Carbon::create(2025, 4, 27, 12, 34, 56));

        $now = now()->format('Y-m-d H:i:s');

        $this->assertEquals('2025-04-27 12:34:56', $now);
    }

    public function test_current_datetime_displayed_correctly()
    {
        $response = $this->actingAs(User::factory()->create())
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y/m/d H:i'));
    }

    /** @test */
    public function test_status_display_when_out_of_work()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /** @test */
    public function test_status_display_when_working()
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHour(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務中');
    }

    /** @test */
    public function test_status_display_when_on_break()
    {
        $user = \App\Models\User::factory()->create();
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(2),
            'clock_out' => null,
        ]);
        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->subHour(),
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /** @test */
    public function test_status_display_when_clocked_out()
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(8),
            'clock_out' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
