<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Journal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin Demo
        $this->call(AdminUserSeeder::class);

        // 2. Akun Guru Demo
        $guru = User::create([
            'nis' => 'GURU001',
            'name' => 'Bapak Ahmad, S.Pd.',
            'email' => 'guru@sekolah.sch.id',
            'role' => 'guru',
            'kelas' => 'XII RPL 1',
            'worship_type' => 'muslim',
            'password' => Hash::make('password123'),
        ]);

        // 3. Akun Siswa Demo 1
        $siswa1 = User::create([
            'nis' => '12345678',
            'name' => 'Budi Santoso',
            'email' => 'budi@sekolah.sch.id',
            'role' => 'siswa',
            'kelas' => 'XII RPL 1',
            'worship_type' => 'muslim',
            'password' => Hash::make('password123'),
        ]);

        // 3. Akun Siswa Demo 2
        $siswa2 = User::create([
            'nis' => '87654321',
            'name' => 'Siti Aminah',
            'email' => 'siti@sekolah.sch.id',
            'role' => 'siswa',
            'kelas' => 'XII RPL 1',
            'worship_type' => 'muslim',
            'password' => Hash::make('password123'),
        ]);

        // 4. Akun Siswa Demo 3 (Non Muslim)
        $siswa3 = User::create([
            'nis' => '55556666',
            'name' => 'Daniel Wijaya',
            'email' => 'daniel@sekolah.sch.id',
            'role' => 'siswa',
            'kelas' => 'XII RPL 1',
            'worship_type' => 'non_muslim',
            'password' => Hash::make('password123'),
        ]);

        // Seed data jurnal 7 hari ke belakang untuk Siswa 1
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $isToday = ($i === 0);

            Journal::create([
                'user_id' => $siswa1->id,
                'date' => $date,
                'bangun_pagi' => true,
                'beribadah' => true,
                'ibadah_details' => [
                    'subuh' => true,
                    'dzuhur' => true,
                    'ashar' => true,
                    'maghrib' => true,
                    'isya' => true,
                ],
                'berolahraga' => true,
                'olahraga_note' => 'Jogging 20 menit',
                'makan_sehat' => true,
                'makan_note' => 'Sayur bayam & telur rebus',
                'gemar_belajar' => true,
                'belajar_note' => 'Membaca modul Pemrograman Web',
                'bermasyarakat' => true,
                'masyarakat_note' => 'Kerja bakti bersama warga',
                'tidur_cepat' => $isToday ? false : true,
                'tidur_note' => '21:30 WIB',
                'completed_count' => $isToday ? 6 : 7,
                'is_fully_completed' => $isToday ? false : true,
            ]);
        }
    }
}
