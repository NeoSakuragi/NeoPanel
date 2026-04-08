<?php

namespace Database\Seeders;

use App\Models\Instance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@neopanel.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
            ],
        );

        Instance::firstOrCreate(
            ['is_self' => true],
            [
                'name' => 'NeoPanel',
                'path' => base_path(),
                'url' => config('app.url'),
                'description' => 'This dashboard',
                'environment' => 'dev',
                'sort_order' => 999,
                'is_active' => true,
            ],
        );
    }
}
