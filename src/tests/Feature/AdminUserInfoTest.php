<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;

class AdminUserInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_info_correctly()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get("/admin/staff/list");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_user_attendance_info_is_displayed_correctly()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertSee((string) $user->name);
    }
}
