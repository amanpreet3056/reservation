<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@reserve.websitedevelopment.cloud'],
            [
                'name' => 'System Admin',
                'phone' => '9814203056',
                'role' => 'admin',
                'password' => Hash::make('Witty132#'),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'manager@reserve.websitedevelopment.cloud'],
            [
                'name' => 'Floor Manager',
                'phone' => null,
                'role' => 'manager',
                'password' => Hash::make('Witty132#'),
            ],
        );
    }
}
