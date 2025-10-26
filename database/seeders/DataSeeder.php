<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\MataPelajaran;

class DataSeeder extends Seeder
{
    public function run(): void
    {
        Kelas::factory()->count(5)->create();
        JadwalPelajaran::factory()->count(8)->create();
        Guru::factory()->count(10)->create();
    }
}
