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
            ['kode_kelas' => 'SMP', 'nama_kelas' => 'Tingkat SMP', 'tingkat' => 'SMP'],
            ['kode_kelas' => 'MA', 'nama_kelas' => 'Tingkat MA', 'tingkat' => 'MA'],
            
            // Classes SMP
            ['kode_kelas' => '7A', 'nama_kelas' => 'Kelas 7A', 'tingkat' => 'SMP'],
            ['kode_kelas' => '7B', 'nama_kelas' => 'Kelas 7B', 'tingkat' => 'SMP'],
            ['kode_kelas' => '8A', 'nama_kelas' => 'Kelas 8A', 'tingkat' => 'SMP'],
            ['kode_kelas' => '8B', 'nama_kelas' => 'Kelas 8B', 'tingkat' => 'SMP'],
            ['kode_kelas' => '9A', 'nama_kelas' => 'Kelas 9A', 'tingkat' => 'SMP'],
            ['kode_kelas' => '9B', 'nama_kelas' => 'Kelas 9B', 'tingkat' => 'SMP'],

            // Classes MA
            ['kode_kelas' => '10-IPA', 'nama_kelas' => 'Kelas 10 IPA', 'tingkat' => 'MA'],
            ['kode_kelas' => '10-IPS', 'nama_kelas' => 'Kelas 10 IPS', 'tingkat' => 'MA'],
            ['kode_kelas' => '11-IPA', 'nama_kelas' => 'Kelas 11 IPA', 'tingkat' => 'MA'],
            ['kode_kelas' => '11-IPS', 'nama_kelas' => 'Kelas 11 IPS', 'tingkat' => 'MA'],
            ['kode_kelas' => '12-IPA', 'nama_kelas' => 'Kelas 12 IPA', 'tingkat' => 'MA'],
            ['kode_kelas' => '12-IPS', 'nama_kelas' => 'Kelas 12 IPS', 'tingkat' => 'MA'],
        ];

        foreach ($kelases as $kelas) {
            Kelas::updateOrCreate(
                ['kode_kelas' => $kelas['kode_kelas']],
                $kelas
            );
        }
    }
}
