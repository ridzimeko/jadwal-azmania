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
            ['nama_guru' => 'Ahmad Dahlan, M.Pd.', 'warna' => '#3b82f6'],
            ['nama_guru' => 'Siti Nurhaliza, S.Pd.', 'warna' => '#ec4899'],
            ['nama_guru' => 'Budi Santoso, M.T.', 'warna' => '#10b981'],
            ['nama_guru' => 'Dewi Lestari, S.S.', 'warna' => '#8b5cf6'],
            ['nama_guru' => 'Eko Prasetyo, M.Si.', 'warna' => '#f59e0b'],
            ['nama_guru' => 'Fitri Handayani, S.Pd.', 'warna' => '#06b6d4'],
            ['nama_guru' => 'Hendra Wijaya, M.Kom.', 'warna' => '#6366f1'],
            ['nama_guru' => 'Indah Permata, S.Pd.', 'warna' => '#14b8a6'],
            ['nama_guru' => 'Joko Widodo, S.Pd.I', 'warna' => '#ef4444'],
            ['nama_guru' => 'Kartika Sari, M.Pd.', 'warna' => '#84cc16'],
        ];

        foreach ($gurus as $guru) {
            Guru::updateOrCreate(
                ['nama_guru' => $guru['nama_guru']],
                $guru
            );
        }
    }
}
