<?php

namespace Database\Seeders;

use App\Models\Guru;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gurus = [
            ['kode_guru' => 'G01', 'nama_guru' => 'Ahmad Dahlan, M.Pd.', 'warna' => '#3b82f6'],
            ['kode_guru' => 'G02', 'nama_guru' => 'Siti Nurhaliza, S.Pd.', 'warna' => '#ec4899'],
            ['kode_guru' => 'G03', 'nama_guru' => 'Budi Santoso, M.T.', 'warna' => '#10b981'],
            ['kode_guru' => 'G04', 'nama_guru' => 'Dewi Lestari, S.S.', 'warna' => '#8b5cf6'],
            ['kode_guru' => 'G05', 'nama_guru' => 'Eko Prasetyo, M.Si.', 'warna' => '#f59e0b'],
            ['kode_guru' => 'G06', 'nama_guru' => 'Fitri Handayani, S.Pd.', 'warna' => '#06b6d4'],
            ['kode_guru' => 'G07', 'nama_guru' => 'Hendra Wijaya, M.Kom.', 'warna' => '#6366f1'],
            ['kode_guru' => 'G08', 'nama_guru' => 'Indah Permata, S.Pd.', 'warna' => '#14b8a6'],
            ['kode_guru' => 'G09', 'nama_guru' => 'Joko Widodo, S.Pd.I', 'warna' => '#ef4444'],
            ['kode_guru' => 'G10', 'nama_guru' => 'Kartika Sari, M.Pd.', 'warna' => '#84cc16'],
        ];

        foreach ($gurus as $guru) {
            Guru::updateOrCreate(
                ['kode_guru' => $guru['kode_guru']],
                $guru
            );
        }
    }
}
