<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(KonsultasiSeeder::class);

        // Akun Admin untuk pengujian PBI-05 (Admin Poli)
        User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Bonamu',
                'no_hp' => '081234567890',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
