<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapels = [
            ['nama_mapel' => 'Matematika', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Ilmu Pengetahuan Alam', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Ilmu Pengetahuan Sosial', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Bahasa Indonesia', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Bahasa Inggris', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Pendidikan Agama Islam', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Pendidikan Jasmani & Kesehatan', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Seni Budaya & Keterampilan', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Fisika', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Biologi', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Kimia', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Ekonomi', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Sejarah', 'jenis_mapel' => 'KBM'],
            ['nama_mapel' => 'Upacara Bendera', 'jenis_mapel' => 'Non KBM'],
            ['nama_mapel' => 'Pramuka & Ekstrakurikuler', 'jenis_mapel' => 'Non KBM'],
        ];

        foreach ($mapels as $mapel) {
            MataPelajaran::updateOrCreate(
                ['nama_mapel' => $mapel['nama_mapel']],
                $mapel
            );
        }
    }
}
