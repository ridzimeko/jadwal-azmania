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
            ['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'IPA', 'nama_mapel' => 'Ilmu Pengetahuan Alam', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'IPS', 'nama_mapel' => 'Ilmu Pengetahuan Sosial', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'BIN', 'nama_mapel' => 'Bahasa Indonesia', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'BIG', 'nama_mapel' => 'Bahasa Inggris', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'PAI', 'nama_mapel' => 'Pendidikan Agama Islam', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'PJOK', 'nama_mapel' => 'Pendidikan Jasmani & Kesehatan', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'SBK', 'nama_mapel' => 'Seni Budaya & Keterampilan', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'FIS', 'nama_mapel' => 'Fisika', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'BIO', 'nama_mapel' => 'Biologi', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'KIM', 'nama_mapel' => 'Kimia', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'EKO', 'nama_mapel' => 'Ekonomi', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'SEJ', 'nama_mapel' => 'Sejarah', 'jenis_mapel' => 'KBM'],
            ['kode_mapel' => 'UPC', 'nama_mapel' => 'Upacara Bendera', 'jenis_mapel' => 'Non KBM'],
            ['kode_mapel' => 'PRM', 'nama_mapel' => 'Pramuka & Ekstrakurikuler', 'jenis_mapel' => 'Non KBM'],
        ];

        foreach ($mapels as $mapel) {
            MataPelajaran::updateOrCreate(
                ['kode_mapel' => $mapel['kode_mapel']],
                $mapel
            );
        }
    }
}
