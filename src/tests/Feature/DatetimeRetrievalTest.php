<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DatetimeRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_datetime_format()
    {
        $now = Carbon::now();
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $now->format('Y-m-d H:i:s'));
    }
}
