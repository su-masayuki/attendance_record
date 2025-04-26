<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $now = Carbon::now();

        foreach ($users as $user) {
            for ($m = 0; $m < 3; $m++) {
                $month = $now->copy()->subMonths($m)->startOfMonth();

                for ($d = 1; $d <= 3; $d++) {
                    $date = $month->copy()->addDays($d - 1);

                    Attendance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date' => $date->format('Y-m-d'),
                        ],
                        [
                            'clock_in' => $date->copy()->setTime(9, 0),
                            'clock_out' => $date->copy()->setTime(17, 0),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}