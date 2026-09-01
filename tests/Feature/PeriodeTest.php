<?php

use App\Models\Periode;
use App\Models\User;
use Livewire\Livewire;

test('periode list page can be rendered', function () {
    $user = User::factory()->create();
    $periode = Periode::create([
        'tahun_ajaran' => '2025/2026',
        'semester' => 'Ganjil',
        'aktif' => true,
    ]);

    $this->actingAs($user);

    $response = $this->get('/jadwal/periode?list=1');
    $response->assertStatus(200);
    $response->assertSee('2025/2026');
});

test('periode can be deleted via livewire action', function () {
    $user = User::factory()->create();
    $periode = Periode::create([
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Genap',
        'aktif' => false,
    ]);

    $this->actingAs($user);

    Livewire::withQueryParams(['list' => 1])
        ->test('pages::jadwal.periode')
        ->call('deletePeriode', $periode->id);

    expect(Periode::find($periode->id))->toBeNull();
});

test('periode can be deleted via filament action', function () {
    $user = User::factory()->create();
    $periode = Periode::create([
        'tahun_ajaran' => '2027/2028',
        'semester' => 'Ganjil',
        'aktif' => false,
    ]);

    $this->actingAs($user);

    Livewire::withQueryParams(['list' => 1])
        ->test('pages::jadwal.periode')
        ->mountAction('delete', ['periode' => $periode->id])
        ->callMountedAction();

    expect(Periode::find($periode->id))->toBeNull();
});
