<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JadwalPelajaran>
 */
class JadwalPelajaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         // daftar hari agar lebih natural
         $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

         // jam acak antara 07:00 sampai 15:00
         $jamMulai = $this->faker->time('H:i', '15:00');
         $durasi = $this->faker->randomElement([40, 60, 90]); // menit
         $jamSelesai = date('H:i', strtotime($jamMulai . " +{$durasi} minutes"));

         return [
             'hari' => $this->faker->randomElement($hariList),
             'jam_mulai' => $jamMulai,
             'jam_selesai' => $jamSelesai,
             'kelas_id' => \App\Models\Kelas::factory(),
             'guru_id' => \App\Models\Guru::factory(),
             'periode_id' => '1',
             'mata_pelajaran_id' => \App\Models\MataPelajaran::factory(),
         ];
    }
}
