<?php

use App\Models\JamPelajaran;
use App\Models\User;

test('jam pelajaran data can be migrated and populated', function () {
    $migration = require database_path('migrations/2026_09_01_000001_update_jam_pelajaran_table_data.php');
    $migration->up();

    expect(JamPelajaran::where('urutan', '0')->first()->jam_mulai)->toBe('07:15:00');
    expect(JamPelajaran::where('urutan', 'Istirahat 1')->first()->jam_mulai)->toBe('09:35:00');
    expect(JamPelajaran::where('urutan', 'Istirahat & Sholat Dhuhur')->first()->jam_mulai)->toBe('11:55:00');
    expect(JamPelajaran::where('urutan', 'Makan, Tidur, & Sholat Ashar')->first()->jam_mulai)->toBe('13:20:00');
    expect(JamPelajaran::where('urutan', '8')->first()->jam_selesai)->toBe('16:30:00');
    expect(JamPelajaran::count())->toBeGreaterThanOrEqual(12);
});
