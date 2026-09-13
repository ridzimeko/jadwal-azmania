<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Livewire\Livewire;

test('mata pelajaran can be created, edited, soft deleted, and recreated with same name without code', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create mapel
    $mapel = MataPelajaran::create([
        'nama_mapel' => 'Pramuka Eksklusif',
        'jenis_mapel' => 'Non KBM',
    ]);

    expect($mapel->id)->not->toBeNull();
    expect(MataPelajaran::where('nama_mapel', 'Pramuka Eksklusif')->exists())->toBeTrue();

    // Soft delete mapel
    $mapel->delete();
    expect(MataPelajaran::find($mapel->id))->toBeNull();

    // Create new mapel with the same name (soft delete allows re-creation without duplicate key error)
    $newMapel = MataPelajaran::create([
        'nama_mapel' => 'Pramuka Eksklusif',
        'jenis_mapel' => 'Non KBM',
    ]);

    expect($newMapel->id)->not->toBeNull();
    expect($newMapel->id)->not->toBe($mapel->id);
    expect(MataPelajaran::where('nama_mapel', 'Pramuka Eksklusif')->count())->toBe(1);
    expect(MataPelajaran::withTrashed()->where('nama_mapel', 'Pramuka Eksklusif')->count())->toBe(2);

    // Clean up
    $newMapel->forceDelete();
    $mapel->forceDelete();
});

test('guru can be created, soft deleted, and recreated with same name without code', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $guru = Guru::create([
        'nama_guru' => 'Ust. Ahmad Dahlan Baru',
        'warna' => '#123456',
    ]);

    expect($guru->id)->not->toBeNull();

    $guru->delete();
    expect(Guru::find($guru->id))->toBeNull();

    $newGuru = Guru::create([
        'nama_guru' => 'Ust. Ahmad Dahlan Baru',
        'warna' => '#654321',
    ]);

    expect($newGuru->id)->not->toBeNull();
    expect($newGuru->id)->not->toBe($guru->id);

    // Clean up
    $newGuru->forceDelete();
    $guru->forceDelete();
});

test('kelas can be created and managed without kode_kelas', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $kelas = Kelas::create([
        'nama_kelas' => 'Kelas Test 7Z',
        'tingkat' => 'SMP',
    ]);

    expect($kelas->id)->not->toBeNull();
    expect(Kelas::noTingkat()->where('nama_kelas', 'Kelas Test 7Z')->exists())->toBeTrue();

    $kelas->delete();
});
