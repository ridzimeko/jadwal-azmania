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

        User::firstOrCreate(
            ['username' => 'superadmin@azmania_id'],
            [
                'nama' => 'Superadmin Azmania',
                'role' => 'superadmin',
                'password' => bcrypt('password'),
            ]
        );

        User::firstOrCreate(
            ['username' => 'kurikulum@azmania_id'],
            [
                'nama' => 'Bagian Kurikulum',
                'password' => bcrypt('password'),
            ]
        );

        $this->call([
            PeriodeSeeder::class,
            JamPelajaranSeeder::class,
            KelasSeeder::class,
            GuruSeeder::class,
            MataPelajaranSeeder::class,
        ]);
    }
}
