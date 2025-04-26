<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $date = $this->faker->date();
        return [
            'user_id' => User::factory(),
            'date' => $date,
            'clock_in' => Carbon::createFromFormat('Y-m-d', $date)->addHours(9), // 9時出勤
            'clock_out' => Carbon::createFromFormat('Y-m-d', $date)->addHours(18), // 18時退勤
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}