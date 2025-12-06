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

        return [
            'hari' => $this->faker->randomElement($hariList),
            'kelas_id' => \App\Models\Kelas::factory(),
            'guru_id' => \App\Models\Guru::factory(),
            'jam_pelajaran_id' => \App\Models\JamPelajaran::factory(),
            'periode_id' => '1',
            'mata_pelajaran_id' => \App\Models\MataPelajaran::factory(),
        ];
    }
}
