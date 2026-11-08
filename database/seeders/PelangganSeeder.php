<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Budi Santoso', 'budi', 'budi@example.com'],
            ['Sinta Maharani', 'sinta', 'sinta@example.com'],
            ['Agus Prasetyo', 'agus', 'agus@example.com'],
            ['Dewi Lestari', 'dewi', 'dewi@example.com'],
            ['Rizky Ramadhan', 'rizky', 'rizky@example.com'],
            ['Nur Aisyah', 'nur', 'nur@example.com'],
        ];

        foreach ($data as [$nama, $username, $email]) {
            Pelanggan::firstOrCreate(
                ['username' => $username],
                ['nama' => $nama, 'email' => $email, 'password' => 'password123']
            );
        }
    }
}
