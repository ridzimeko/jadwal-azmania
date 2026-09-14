<?php

use App\Helpers\JadwalHelper;
use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Periode;
use App\Models\JadwalPelajaran;
use Livewire\Livewire;

test('JadwalHelper findAvailableSlotsAcrossDays returns available days excluding current day', function () {
    $periode = Periode::firstOrCreate(
        ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil'],
        ['aktif' => true]
    );

    $kelas = Kelas::firstOrCreate(
        ['nama_kelas' => '7A Rekomendasi'],
        ['tingkat' => 'SMP']
    );

    $guru = Guru::firstOrCreate(
        ['nama_guru' => 'Ustadz Rekomendasi'],
        ['warna' => '#00aa00']
    );

    $mapel = MataPelajaran::firstOrCreate(
        ['nama_mapel' => 'Fikih Rekomendasi'],
        ['jenis_mapel' => 'KBM']
    );

    $jam1 = JamPelajaran::firstOrCreate(
        ['urutan' => '1'],
        ['jam_mulai' => '07:15', 'jam_selesai' => '08:00', 'kategori' => 'KBM']
    );

    $jam2 = JamPelajaran::firstOrCreate(
        ['urutan' => '2'],
        ['jam_mulai' => '08:00', 'jam_selesai' => '08:45', 'kategori' => 'KBM']
    );

    // Buat jadwal yang mengisi hari Senin jam 1 dan jam 2
    $jadwalSenin1 = JadwalPelajaran::create([
        'hari' => 'Senin',
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'mata_pelajaran_id' => $mapel->id,
        'jam_pelajaran_id' => $jam1->id,
        'periode_id' => $periode->id,
    ]);

    $jadwalSenin2 = JadwalPelajaran::create([
        'hari' => 'Senin',
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'mata_pelajaran_id' => $mapel->id,
        'jam_pelajaran_id' => $jam2->id,
        'periode_id' => $periode->id,
    ]);

    // Cari rekomendasi di hari lain untuk kelas & guru tersebut dengan kebutuhan 2 JP, mengecualikan hari Senin
    $data = [
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'periode_id' => $periode->id,
        'hari' => 'Senin',
    ];

    $recommendations = JadwalHelper::findAvailableSlotsAcrossDays($data, null, 2, 'Senin');

    expect($recommendations)->toBeArray();
    expect(count($recommendations))->toBeGreaterThan(0);

    // Hari Senin tidak boleh ada dalam rekomendasi karena merupakan hari yang aktif
    $daysInRec = array_column($recommendations, 'hari');
    expect($daysInRec)->not->toContain('Senin');
    expect($daysInRec)->toContain('Selasa');

    // Cek bahwa Selasa memiliki consecutive blocks untuk 2 JP
    $selasaRec = collect($recommendations)->firstWhere('hari', 'Selasa');
    expect($selasaRec)->not->toBeNull();
    expect($selasaRec['total_available'])->toBeGreaterThanOrEqual(2);
    expect($selasaRec['consecutive_blocks'])->not->toBeEmpty();

    // Clean up
    $jadwalSenin1->delete();
    $jadwalSenin2->delete();
});

test('Livewire Jadwal generates otherDaysRecommendations on bentrok and allows applyRecommendation', function () {
    $user = \App\Models\User::factory()->create(['role' => 'admin']);
    $this->actingAs($user);

    $periode = Periode::firstOrCreate(
        ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil'],
        ['aktif' => true]
    );

    $kelas = Kelas::firstOrCreate(
        ['nama_kelas' => '8B Rekomendasi'],
        ['tingkat' => 'SMP']
    );

    $guru = Guru::firstOrCreate(
        ['nama_guru' => 'Ustadzah Rekomendasi'],
        ['warna' => '#aa00aa']
    );

    $mapel = MataPelajaran::firstOrCreate(
        ['nama_mapel' => 'Bahasa Arab Rekomendasi'],
        ['jenis_mapel' => 'KBM']
    );

    $jam1 = JamPelajaran::firstOrCreate(
        ['urutan' => '1'],
        ['jam_mulai' => '07:15', 'jam_selesai' => '08:00', 'kategori' => 'KBM']
    );

    $jam2 = JamPelajaran::firstOrCreate(
        ['urutan' => '2'],
        ['jam_mulai' => '08:00', 'jam_selesai' => '08:45', 'kategori' => 'KBM']
    );

    // Existing schedule on Senin Jam 1
    $existing = JadwalPelajaran::create([
        'hari' => 'Senin',
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'mata_pelajaran_id' => $mapel->id,
        'jam_pelajaran_id' => $jam1->id,
        'periode_id' => $periode->id,
    ]);

    // Test livewire component
    $testComponent = Livewire::test('pages::jadwal.index', ['periode_id' => $periode->id])
        ->set('periode_id', $periode->id)
        ->set('formData', [
            'hari' => 'Senin',
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'mata_pelajaran_id' => $mapel->id,
            'jam_pelajaran_ids' => [(string) $jam1->id],
        ])
        ->call('save');

    // Bentrok should occur
    $testComponent->assertSet('isEdit', false);
    $bentrokList = $testComponent->get('jadwalBentrokList');
    expect($bentrokList)->not->toBeEmpty();

    $recs = $testComponent->get('otherDaysRecommendations');
    expect($recs)->not->toBeEmpty();
    $daysInRec = array_column($recs, 'hari');
    expect($daysInRec)->not->toContain('Senin');

    // Apply recommendation to Tuesday
    $testComponent->call('applyRecommendation', 'Selasa', [(string) $jam2->id]);
    expect($testComponent->get('formData.hari'))->toBe('Selasa');
    expect($testComponent->get('formData.jam_pelajaran_ids'))->toBe([(string) $jam2->id]);
    expect($testComponent->get('jadwalBentrokList'))->toBeEmpty();
    expect($testComponent->get('currentDayRecommendations'))->toBeNull();
    expect($testComponent->get('otherDaysRecommendations'))->toBeEmpty();

    // Clean up
    $existing->delete();
});

test('JadwalHelper getSmartScheduleRecommendations prioritizes current day if slots are available', function () {
    $periode = Periode::firstOrCreate(
        ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil'],
        ['aktif' => true]
    );

    $kelas = Kelas::firstOrCreate(
        ['nama_kelas' => '9C Rekomendasi'],
        ['tingkat' => 'SMP']
    );

    $guru = Guru::firstOrCreate(
        ['nama_guru' => 'Ustadz Cerdas'],
        ['warna' => '#112233']
    );

    $mapel = MataPelajaran::firstOrCreate(
        ['nama_mapel' => 'IPA Rekomendasi'],
        ['jenis_mapel' => 'KBM']
    );

    $jam1 = JamPelajaran::firstOrCreate(
        ['urutan' => '1'],
        ['jam_mulai' => '07:15', 'jam_selesai' => '08:00', 'kategori' => 'KBM']
    );

    $jam2 = JamPelajaran::firstOrCreate(
        ['urutan' => '2'],
        ['jam_mulai' => '08:00', 'jam_selesai' => '08:45', 'kategori' => 'KBM']
    );

    // Hari Senin Jam 1 bentrok (terisi)
    $jadwal1 = JadwalPelajaran::create([
        'hari' => 'Senin',
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'mata_pelajaran_id' => $mapel->id,
        'jam_pelajaran_id' => $jam1->id,
        'periode_id' => $periode->id,
    ]);

    // Data user yang ingin menginput di hari Senin 1 JP
    $data = [
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'periode_id' => $periode->id,
        'hari' => 'Senin',
    ];

    $smart = JadwalHelper::getSmartScheduleRecommendations($data, null, 1, 'Senin');

    // Prioritas 1: current_day harus ada dan has_match = true karena Jam 2 di hari Senin masih kosong
    expect($smart['current_day'])->not->toBeNull();
    expect($smart['current_day']['hari'])->toBe('Senin');
    expect($smart['current_day']['has_match'])->toBeTrue();
    expect($smart['current_day']['consecutive_blocks'])->not->toBeEmpty();

    // Prioritas 2: other_days tetap disiapkan sebagai alternatif
    expect($smart['other_days'])->toBeArray();
    expect(count($smart['other_days']))->toBeGreaterThan(0);
    $otherDaysList = array_column($smart['other_days'], 'hari');
    expect($otherDaysList)->not->toContain('Senin');

    // Clean up
    $jadwal1->delete();
});
