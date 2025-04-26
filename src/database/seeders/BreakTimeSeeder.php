<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BreakTime;
use App\Models\Attendance;

class BreakTimeSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => '12:00:00',
                'break_end' => '12:30:00',
            ]);
        }
    }
}