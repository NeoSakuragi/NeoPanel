<?php

namespace Database\Seeders;

use App\Models\Application;
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

        $panelApp = Application::firstOrCreate(
            ['name' => 'NeoPanel'],
            [
                'github_repo' => 'NeoSakuragi/NeoPanel',
                'description' => 'Instance management dashboard',
                'sort_order' => 999,
                'is_active' => true,
            ],
        );

        Instance::firstOrCreate(
            ['is_self' => true],
            [
                'application_id' => $panelApp->id,
                'name' => 'NeoPanel',
                'path' => base_path(),
                'url' => config('app.url'),
                'environment' => 'dev',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );
    }
}
