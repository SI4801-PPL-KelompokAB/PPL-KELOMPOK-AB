<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name'  => 'Admin Bonamu',
                'email' => 'admin@bonamu.test',
                'no_hp' => '081200000001',
                'role'  => 'admin',
            ],
            [
                'name'  => 'Petugas Bonamu',
                'email' => 'petugas@bonamu.test',
                'no_hp' => '081200000002',
                'role'  => 'petugas',
            ],
        ];

        foreach ($accounts as $account) {
            // firstOrNew + forceFill supaya tidak bergantung pada $fillable di model User
            $user = User::firstOrNew(['email' => $account['email']]);

            $user->forceFill([
                'name'              => $account['name'],
                'no_hp'             => $account['no_hp'],
                'role'              => $account['role'],
                'is_active'         => true,
                'email_verified_at' => now(),
                'password'          => Hash::make('password123'),
            ])->save();
        }
    }
}