<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guru>
 */
class GuruFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nip' => $this->faker->unique()->numerify(str_repeat('#', 18)), // max 24, biasanya NIP 18 digit
            'nama_guru' => $this->faker->name(), // contoh: "Budi Santoso"
            'warna' => sprintf('#%06X', mt_rand(0xAAAAAA, 0xFFFFFF)),
        ];
    }
}
