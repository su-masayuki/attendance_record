<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_attendance_list()
    {
        
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');

   
        $response = $this->get('/admin/attendance/list');

      
        $response->assertStatus(200);

     
        $response->assertSee('勤怠一覧');
    }
}
