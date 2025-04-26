<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Attendance;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_correct_attendance_detail()
    {
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee((string) $attendance->date);
    }

    public function test_error_when_clock_in_after_clock_out()
    {
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->put("/admin/attendance/{$attendance->id}", [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_error_when_break_start_after_clock_out()
    {
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->put("/admin/attendance/{$attendance->id}", [
            'breaks' => [['start' => '20:00', 'end' => '21:00']],
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_error_when_break_end_after_clock_out()
    {
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->put("/admin/attendance/{$attendance->id}", [
            'breaks' => [['start' => '12:00', 'end' => '20:00']],
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_error_when_note_is_missing()
    {
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->put("/admin/attendance/{$attendance->id}", [
            'note' => '',
        ]);

        $response->assertSessionHasErrors('note');
    }
}