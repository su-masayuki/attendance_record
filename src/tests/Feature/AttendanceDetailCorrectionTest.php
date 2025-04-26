<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceDetailCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_in_must_be_before_clock_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->patch("/attendance/{$attendance->id}", [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'note' => 'Test',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_break_start_must_be_before_clock_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->patch("/attendance/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '19:00', 'end' => '20:00'],
            ],
            'note' => 'Test',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_break_end_must_be_before_clock_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->patch("/attendance/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '17:00', 'end' => '19:00'],
            ],
            'note' => 'Test',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_note_is_required()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->patch("/attendance/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '',
        ]);

        $response->assertSessionHasErrors('note');
    }

    public function test_successful_correction_submission()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->patch("/attendance/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => 'Correction request',
        ]);

        $response->assertSessionHasNoErrors();
    }
}
