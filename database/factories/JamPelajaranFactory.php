<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JamPelajaran>
 */
class JamPelajaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // jam acak antara 07:00 sampai 15:00
        $jamMulai = $this->faker->time('H:i', '15:00');
        $durasi = $this->faker->randomElement([40, 60, 90]); // menit
        $jamSelesai = date('H:i', strtotime($jamMulai . " +{$durasi} minutes"));

        return [
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'urutan' => $this->faker->unique()->numberBetween(1, 20),
        ];
    }
}
