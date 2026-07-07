<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'user_name' => '管理者',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 1,
        ]);

        User::create([
            'user_name' => 'テストユーザー',
            'email' => 'test1@test',
            'password' => Hash::make('test1234'),
            'role' => 0,
        ]);
    }
}