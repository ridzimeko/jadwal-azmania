<?php

use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Periode;
use App\Models\JadwalPelajaran;

test('mata pelajaran can be soft deleted and retains relation in jadwal_pelajaran', function () {
    $mapel = MataPelajaran::create([
        'kode_mapel' => 'TESTMP01',
        'nama_mapel' => 'Testing Soft Delete Mapel',
        'jenis_mapel' => 'KBM',
    ]);

    $jam = JamPelajaran::firstOrCreate(
        ['urutan' => '1'],
        ['jam_mulai' => '07:00', 'jam_selesai' => '07:45', 'kategori' => 'KBM']
    );

    $periode = Periode::firstOrCreate(
        ['tahun_ajaran' => '2025/2026', 'semester' => 'Ganjil'],
        ['aktif' => true]
    );

    $jadwal = JadwalPelajaran::create([
        'hari' => 'Senin',
        'mata_pelajaran_id' => $mapel->id,
        'jam_pelajaran_id' => $jam->id,
        'periode_id' => $periode->id,
    ]);

    // Perform soft delete
    $mapel->delete();

    // Check soft delete status
    expect(MataPelajaran::find($mapel->id))->toBeNull();
    expect(MataPelajaran::withTrashed()->find($mapel->id))->not->toBeNull();
    expect(MataPelajaran::withTrashed()->find($mapel->id)->deleted_at)->not->toBeNull();

    // Check jadwal still exists and still links to mapel
    $jadwalFresh = JadwalPelajaran::find($jadwal->id);
    expect($jadwalFresh)->not->toBeNull();
    expect($jadwalFresh->mataPelajaran)->not->toBeNull();
    expect($jadwalFresh->mataPelajaran->nama_mapel)->toBe('Testing Soft Delete Mapel');

    // Clean up
    $jadwal->delete();
    $mapel->forceDelete();
});

test('guru can be soft deleted and retains relation in jadwal_pelajaran', function () {
    $mapel = MataPelajaran::firstOrCreate(
        ['kode_mapel' => 'MAPELDEF'],
        ['nama_mapel' => 'Mapel Default', 'jenis_mapel' => 'KBM']
    );

    $guru = Guru::create([
        'kode_guru' => 'GRTEST01',
        'nama_guru' => 'Testing Guru Soft Delete',
        'warna' => '#123456',
    ]);

    $jam = JamPelajaran::firstOrCreate(
        ['urutan' => '1'],
        ['jam_mulai' => '07:00', 'jam_selesai' => '07:45', 'kategori' => 'KBM']
    );

    $periode = Periode::firstOrCreate(
        ['tahun_ajaran' => '2025/2026', 'semester' => 'Ganjil'],
        ['aktif' => true]
    );

    $jadwal = JadwalPelajaran::create([
        'hari' => 'Senin',
        'mata_pelajaran_id' => $mapel->id,
        'guru_id' => $guru->id,
        'jam_pelajaran_id' => $jam->id,
        'periode_id' => $periode->id,
    ]);

    // Perform soft delete
    $guru->delete();

    // Check soft delete status
    expect(Guru::find($guru->id))->toBeNull();
    expect(Guru::withTrashed()->find($guru->id))->not->toBeNull();
    expect(Guru::withTrashed()->find($guru->id)->deleted_at)->not->toBeNull();

    // Check jadwal still exists and still links to guru
    $jadwalFresh = JadwalPelajaran::find($jadwal->id);
    expect($jadwalFresh)->not->toBeNull();
    expect($jadwalFresh->guru)->not->toBeNull();
    expect($jadwalFresh->guru->nama_guru)->toBe('Testing Guru Soft Delete');

    // Clean up
    $jadwal->delete();
    $guru->forceDelete();
});
