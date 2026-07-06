<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // factory() を使わずに、直接 create するんや！
        User::create([
            'user_name' => '一般ユーザーA',
            'email'     => 'user@example.com',
            'password'  => bcrypt('password123'), // パスワードをハッシュ化
            'role'      => 0,                     // 0:一般
        ]);

        User::create([
            'user_name' => '管理者ユーザー',
            'email'     => 'admin@example.com',
            'password'  => bcrypt('admin123'),    // パスワードをハッシュ化
            'role'      => 1,                     // 1:管理者
        ]);
    }
}
