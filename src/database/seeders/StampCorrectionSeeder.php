<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StampCorrection;
use App\Models\Attendance;
use Illuminate\Support\Str;

class StampCorrectionSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            StampCorrection::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'clock_in' => optional($attendance->clock_in)->subMinutes(10),
                'clock_out' => optional($attendance->clock_out)->addMinutes(10),
                'target_date' => $attendance->date,
                'status' => '承認待ち',
                'reason' => 'テスト修正申請',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}