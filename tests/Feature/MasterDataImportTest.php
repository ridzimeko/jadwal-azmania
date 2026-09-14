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

test('downloadable excel templates have clean column dimensions', function () {
    $templates = ['template_guru.xlsx', 'template_kelas.xlsx', 'template_mapel.xlsx'];

    foreach ($templates as $t) {
        $path = public_path("templates/{$t}");
        expect(file_exists($path))->toBeTrue();

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($path);
        $highestCol = $spreadsheet->getActiveSheet()->getHighestColumn();
        $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        // All clean templates must have 10 columns or fewer (never 16384 / XFD)
        expect($colIndex)->toBeLessThanOrEqual(10);
    }
});

test('jadwal pelajaran import pre-fetches master data and creates records properly', function () {
    $periode = \App\Models\Periode::firstOrCreate(['tahun_ajaran' => '2026/2027 Test', 'semester' => 'Ganjil']);
    $guru = Guru::firstOrCreate(['nama_guru' => 'Guru Import Prefetch Test'], ['warna' => '#123456']);
    $kelas = Kelas::firstOrCreate(['nama_kelas' => '7-X Test'], ['tingkat' => 'SMP']);
    $mapel = MataPelajaran::firstOrCreate(['nama_mapel' => 'Mapel Import Prefetch Test'], ['jenis_mapel' => 'KBM']);
    $jam = \App\Models\JamPelajaran::firstOrCreate(['urutan' => 99], ['jam_mulai' => '17:00', 'jam_selesai' => '17:45']);

    $import = new \App\Imports\JadwalPelajaranImport($periode->id);
    $collection = new Collection([
        [
            'nama_kelas' => '7-X Test',
            'nama_mata_pelajaran' => 'Mapel Import Prefetch Test',
            'nama_guru_pengajar' => 'Guru Import Prefetch Test',
            'hari' => 'Senin',
            'jam_ke' => '99',
        ],
    ]);

    $import->collection($collection);

    expect($import->getImportedCount())->toBe(1);

    $jadwal = \App\Models\JadwalPelajaran::where('periode_id', $periode->id)
        ->where('kelas_id', $kelas->id)
        ->where('mata_pelajaran_id', $mapel->id)
        ->where('guru_id', $guru->id)
        ->where('hari', 'Senin')
        ->where('jam_pelajaran_id', $jam->id)
        ->first();

    expect($jadwal)->not->toBeNull();

    // Clean up
    $jadwal?->delete();
    $jam->delete();
    $mapel->forceDelete();
    $kelas->delete();
    $guru->forceDelete();
    $periode->delete();
});
