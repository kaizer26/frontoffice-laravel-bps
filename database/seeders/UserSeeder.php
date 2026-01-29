<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // Create sample petugas
        User::create([
            'nip_bps' => '123456789',
            'nip_pns' => '199001012015011001',
            'name' => 'Petugas Satu',
            'email' => 'petugas1@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'petugas',
            'status' => 'aktif',
        ]);

        User::create([
            'nip_bps' => '987654321',
            'nip_pns' => '199002022015011002',
            'name' => 'Petugas Dua',
            'email' => 'petugas2@bps.go.id',
            'password' => Hash::make('password'),
            'role' => 'petugas',
            'status' => 'aktif',
        ]);
    }
}
