<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kelas::create([
            'kode_kelas' => 'SMP',
            'nama_kelas' => 'Tingkat SMP',
            'tingkat' => 'SMP',
        ]);

         Kelas::create([
            'kode_kelas' => 'MA',
            'nama_kelas' => 'Tingkat MA',
            'tingkat' => 'MA',
        ]);
    }
}
