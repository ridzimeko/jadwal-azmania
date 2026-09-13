<?php

use App\Imports\GuruImport;
use App\Imports\KelasImport;
use App\Imports\MapelImport;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Support\Collection;

test('mapel import ignores kode and jatah per pekan', function () {
    $import = new MapelImport();
    $collection = new Collection([
        [
            'kode_mapel' => 'KODEXX1',
            'mata_pelajaran' => 'Robotik & AI',
            'jenis_mapel' => 'Non KBM',
            'jp_per_pekan' => 4,
            'jatah_per_pekan' => 4,
        ],
        [
            'kode_mapel' => 'KODEXX2',
            'mata_pelajaran' => 'Biologi Terapan',
            'jenis_mapel' => 'KBM',
            'jp_per_pekan' => 2,
        ],
    ]);

    $import->collection($collection);

    expect(MataPelajaran::where('nama_mapel', 'Robotik & AI')->first())->not->toBeNull();
    expect(MataPelajaran::where('nama_mapel', 'Robotik & AI')->first()->jenis_mapel)->toBe('Non KBM');
    expect(MataPelajaran::where('nama_mapel', 'Biologi Terapan')->first())->not->toBeNull();

    // Clean up
    MataPelajaran::whereIn('nama_mapel', ['Robotik & AI', 'Biologi Terapan'])->forceDelete();
});

test('guru import ignores kode_guru', function () {
    $import = new GuruImport();
    $collection = new Collection([
        [
            'kode_guru' => 'G999',
            'nama_guru' => 'Guru Import Testing',
            'warna' => '#abcdef',
        ],
    ]);

    $import->collection($collection);

    $guru = Guru::where('nama_guru', 'Guru Import Testing')->first();
    expect($guru)->not->toBeNull();
    expect($guru->warna)->toBe('#abcdef');

    // Clean up
    $guru->forceDelete();
});

test('kelas import ignores kode_kelas', function () {
    $import = new KelasImport();
    $collection = new Collection([
        [
            'kode_kelas' => 'KLSTEST99',
            'nama_kelas' => 'Kelas 9Z Test',
            'tingkat' => 'SMP',
        ],
    ]);

    $import->collection($collection);

    $kelas = Kelas::where('nama_kelas', 'Kelas 9Z Test')->first();
    expect($kelas)->not->toBeNull();
    expect($kelas->tingkat)->toBe('SMP');

    // Clean up
    $kelas->delete();
});
