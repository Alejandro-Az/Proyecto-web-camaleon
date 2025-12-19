<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'client.demo@camaleon.test'],
            [
                'name' => 'Cliente Demo',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CLIENT,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin.demo@camaleon.test'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );
    }
}
