<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kelas>
 */
class KelasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tingkat = $this->faker->randomElement(['SMP', 'MA']);
        $namaKelas = $tingkat . ' ' . $this->faker->randomElement(['VII', 'VIII', 'IX', 'X', 'XI', 'XII']) . '-' . $this->faker->randomLetter();

        return [
            'nama_kelas' => $namaKelas, // contoh: SMP VIII-B
            'tingkat' => $tingkat,
        ];
    }
}
