<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['nis' => 'ADMIN001'],
            [
                'name' => 'Admin Sekolah',
                'email' => 'admin@sekolah.sch.id',
                'role' => 'admin',
                'kelas' => '-',
                'worship_type' => 'muslim',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
