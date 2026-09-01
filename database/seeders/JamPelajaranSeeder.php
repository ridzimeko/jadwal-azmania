<?php

namespace Database\Seeders;

use App\Models\JamPelajaran;
use Illuminate\Database\Seeder;

class JamPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jams = [
            ['urutan' => '0', 'jam_mulai' => '07:15:00', 'jam_selesai' => '08:15:00'],
            ['urutan' => '1', 'jam_mulai' => '08:15:00', 'jam_selesai' => '08:55:00'],
            ['urutan' => '2', 'jam_mulai' => '08:55:00', 'jam_selesai' => '09:35:00'],
            ['urutan' => 'Istirahat 1', 'jam_mulai' => '09:35:00', 'jam_selesai' => '09:55:00'],
            ['urutan' => '3', 'jam_mulai' => '09:55:00', 'jam_selesai' => '10:35:00'],
            ['urutan' => '4', 'jam_mulai' => '10:35:00', 'jam_selesai' => '11:15:00'],
            ['urutan' => '5', 'jam_mulai' => '11:15:00', 'jam_selesai' => '11:55:00'],
            ['urutan' => 'Istirahat & Sholat Dhuhur', 'jam_mulai' => '11:55:00', 'jam_selesai' => '12:40:00'],
            ['urutan' => '6', 'jam_mulai' => '12:40:00', 'jam_selesai' => '13:20:00'],
            ['urutan' => 'Makan, Tidur, & Sholat Ashar', 'jam_mulai' => '13:20:00', 'jam_selesai' => '15:30:00'],
            ['urutan' => '7', 'jam_mulai' => '15:30:00', 'jam_selesai' => '16:00:00'],
            ['urutan' => '8', 'jam_mulai' => '16:00:00', 'jam_selesai' => '16:30:00'],
        ];

        foreach ($jams as $jam) {
            JamPelajaran::updateOrCreate(
                ['urutan' => $jam['urutan']],
                $jam
            );
        }
    }
}
