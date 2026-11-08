<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // GANTI password ini setelah login pertama
        Admin::firstOrCreate(
            ['admin_username' => 'admin'],
            ['admin_password' => 'admin12345']
        );
    }
}
