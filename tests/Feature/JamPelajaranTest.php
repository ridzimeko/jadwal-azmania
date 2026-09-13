<?php

use App\Models\JamPelajaran;
use Database\Seeders\JamPelajaranSeeder;

test('jam pelajaran data can be seeded and starts from 1', function () {
    $seeder = new JamPelajaranSeeder();
    $seeder->run();

    expect(JamPelajaran::where('urutan', '0')->first())->toBeNull();
    expect(JamPelajaran::where('urutan', '1')->first()->jam_mulai)->toBe('07:15:00');
    expect(JamPelajaran::where('urutan', 'Istirahat 1')->first()->jam_mulai)->toBe('09:35:00');
    expect(JamPelajaran::where('urutan', 'Istirahat & Sholat Dhuhur')->first()->jam_mulai)->toBe('11:55:00');
    expect(JamPelajaran::where('urutan', 'Makan, Tidur, & Sholat Ashar')->first()->jam_mulai)->toBe('13:20:00');
    expect(JamPelajaran::where('urutan', '9')->first()->jam_selesai)->toBe('16:30:00');
    expect(JamPelajaran::count())->toBeGreaterThanOrEqual(12);
});
