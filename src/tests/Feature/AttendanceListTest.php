<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_attendance_list_screen_can_be_rendered()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
        ]);
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('勤怠一覧'); 
    }

    
    public function test_can_navigate_to_previous_month()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-03-10',
        ]);
        $response = $this->actingAs($user)->get('/attendance/list?month=2025-03');
        $response->assertStatus(200);
        $response->assertSee('2025年3月'); 
    }

  
    public function test_can_navigate_to_next_month()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-05-10',
        ]);
        $response = $this->actingAs($user)->get('/attendance/list?month=2025-05');
        $response->assertStatus(200);
        $response->assertSee('2025年5月'); 
    }

 
    public function test_can_navigate_to_attendance_detail()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細'); 
    }
}
