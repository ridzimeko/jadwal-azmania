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
            ['urutan' => '1', 'jam_mulai' => '07:30:00', 'jam_selesai' => '08:15:00'],
            ['urutan' => '2', 'jam_mulai' => '08:15:00', 'jam_selesai' => '09:00:00'],
            ['urutan' => '3', 'jam_mulai' => '09:00:00', 'jam_selesai' => '09:45:00'],
            ['urutan' => '4', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:00:00'],
            ['urutan' => '5', 'jam_mulai' => '11:00:00', 'jam_selesai' => '11:45:00'],
            ['urutan' => '6', 'jam_mulai' => '12:30:00', 'jam_selesai' => '13:15:00'],
            ['urutan' => '7', 'jam_mulai' => '13:15:00', 'jam_selesai' => '14:00:00'],
            ['urutan' => '8', 'jam_mulai' => '14:00:00', 'jam_selesai' => '14:45:00'],
        ];

        foreach ($jams as $jam) {
            JamPelajaran::updateOrCreate(
                ['urutan' => $jam['urutan']],
                $jam
            );
        }
    }
}
