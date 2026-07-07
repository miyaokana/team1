<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::create([
            'name' => 'テスト株式会社',
        ]);

        User::create([
            'company_id' => $company->id,
            'user_name' => '管理者',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 1,
        ]);

        User::create([
            'company_id' => $company->id,
            'user_name' => 'テストユーザー',
            'email' => 'test1@test',
            'password' => Hash::make('test1234'),
            'role' => 0,
        ]);
    }
}
