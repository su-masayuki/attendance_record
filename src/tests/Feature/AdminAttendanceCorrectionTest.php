<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Feature tests for admin attendance correction approval process.
 */
class AdminAttendanceCorrectionTest extends TestCase
{
    public function test_admin_can_view_pending_corrections()
    {
        $response = $this->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
    }

    public function test_admin_can_approve_correction_request()
    {
        // Example: Assume we have a correction request with id 1
        $response = $this->post('/admin/stamp_correction_request/approve/1');

        $response->assertRedirect('/admin/stamp_correction_request/list');
    }
}
