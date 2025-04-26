<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(StaffSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(AttendanceSeeder::class);
        $this->call(BreakTimeSeeder::class);
        $this->call(StampCorrectionSeeder::class);
    }
}
