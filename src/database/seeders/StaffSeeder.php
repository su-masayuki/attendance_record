<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Userモデルを使用
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffSeeder extends Seeder
{
    public function run()
    {
        $staffs = [
            ['name' => '西 伶奈', 'email' => 'reina.n@coachtech.com'],
            ['name' => '山田 太郎', 'email' => 'taro.y@coachtech.com'],
            ['name' => '増田 一世', 'email' => 'issei.m@coachtech.com'],
            ['name' => '山本 敬吉', 'email' => 'keikichi.y@coachtech.com'],
            ['name' => '秋田 朋美', 'email' => 'tomomi.a@coachtech.com'],
            ['name' => '中西 教夫', 'email' => 'norio.n@coachtech.com'],
        ];

        foreach ($staffs as $staff) {
            User::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'name' => $staff['name'],
                    'email' => $staff['email'],
                    'password' => Hash::make('password'), // デフォルトパスワード
                    'is_admin' => false, // 一般ユーザーとして登録
                ]
            );
        }
    }
}