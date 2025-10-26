<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KegiatanFactory extends Factory
{
    public function definition(): array
    {
        $list = [
            ['kode_kegiatan' => 'K001', 'nama_kegiatan' => 'Doa Pagi', 'warna' => '#16A34A'],
            ['kode_kegiatan' => 'K002', 'nama_kegiatan' => 'Upacara Bendera', 'warna' => '#2563EB'],
            ['kode_kegiatan' => 'K003', 'nama_kegiatan' => 'Istirahat', 'warna' => '#F59E0B'],
            ['kode_kegiatan' => 'K004', 'nama_kegiatan' => 'Senam Pagi', 'warna' => '#EF4444'],
            ['kode_kegiatan' => 'K005', 'nama_kegiatan' => 'Kegiatan Kelas', 'warna' => '#7C3AED'],
        ];

        return $this->faker->randomElement($list);
    }
}
