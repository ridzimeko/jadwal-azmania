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
            'kode_guru' => $this->faker->unique()->numerify(str_repeat('#', 10)), // max 24, biasanya NIP 18 digit
            'nama_guru' => $this->faker->name(), // contoh: "Budi Santoso"
        ];
    }
}
