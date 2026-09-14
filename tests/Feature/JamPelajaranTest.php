<?php

use App\Models\JamPelajaran;
use Database\Seeders\JamPelajaranSeeder;

test('jam pelajaran data can be seeded and starts from 1', function () {
    $seeder = new JamPelajaranSeeder();
    $seeder->run();

    expect(JamPelajaran::where('urutan', '0')->first())->toBeNull();
    expect(JamPelajaran::where('urutan', '1')->first()->jam_mulai)->toBe('07:15');
    expect(JamPelajaran::where('urutan', '12')->first()->jam_selesai)->toBe('16:30');
    expect(JamPelajaran::count())->toBeGreaterThanOrEqual(12);
});
