<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            AnalyticsSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@kotb.club'],
            [
                'name' => 'KOTB Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );
    }
}
