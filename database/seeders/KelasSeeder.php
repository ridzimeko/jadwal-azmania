<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelases = [
            // Global Level
            ['nama_kelas' => 'Tingkat SMP', 'tingkat' => 'SMP'],
            ['nama_kelas' => 'Tingkat MA', 'tingkat' => 'MA'],
            
            // Classes SMP
            ['nama_kelas' => 'Kelas 7A', 'tingkat' => 'SMP'],
            ['nama_kelas' => 'Kelas 7B', 'tingkat' => 'SMP'],
            ['nama_kelas' => 'Kelas 8A', 'tingkat' => 'SMP'],
            ['nama_kelas' => 'Kelas 8B', 'tingkat' => 'SMP'],
            ['nama_kelas' => 'Kelas 9A', 'tingkat' => 'SMP'],
            ['nama_kelas' => 'Kelas 9B', 'tingkat' => 'SMP'],

            // Classes MA
            ['nama_kelas' => 'Kelas 10 IPA', 'tingkat' => 'MA'],
            ['nama_kelas' => 'Kelas 10 IPS', 'tingkat' => 'MA'],
            ['nama_kelas' => 'Kelas 11 IPA', 'tingkat' => 'MA'],
            ['nama_kelas' => 'Kelas 11 IPS', 'tingkat' => 'MA'],
            ['nama_kelas' => 'Kelas 12 IPA', 'tingkat' => 'MA'],
            ['nama_kelas' => 'Kelas 12 IPS', 'tingkat' => 'MA'],
        ];

        foreach ($kelases as $kelas) {
            Kelas::updateOrCreate(
                ['nama_kelas' => $kelas['nama_kelas']],
                $kelas
            );
        }
    }
}
