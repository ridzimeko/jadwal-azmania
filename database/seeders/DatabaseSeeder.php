<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'nama' => 'Superadmin Azmania',
            'username' => 'superadmin@azmania_id',
            'role' => 'superadmin'
        ]);

        User::factory()->create([
            'nama' => 'Bagian Kurikulum',
            'username' => 'kurikulum@azmania_id',
        ]);

        $this->call([
            KelasSeeder::class,
            PeriodeSeeder::class,
            DataSeeder::class,
        ]);
    }
}
