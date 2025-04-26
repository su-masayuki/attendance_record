<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理太郎',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'guard_name' => 'admin',
            ]
        );
    }
}