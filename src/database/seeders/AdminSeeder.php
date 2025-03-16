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
            ['email' => 'admin@example.com'], // 既存の管理者がいれば更新
            [
                'name' => '管理太郎',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'), // 必要に応じて変更
                'is_admin' => true,
                'guard_name' => 'admin',
            ]
        );
    }
}