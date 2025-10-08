<?php

use Livewire\Volt\Component;

new class extends Component {
    public $kelas_id;
    public $mapel_id;
    public $guru_id;
    public $hari;
    public $jam_mulai;
    public $jam_selesai;

    public $tingkat;

    public function mount($tingkat)
    {
        $this->tingkat = $tingkat;
    }
}; ?>

<div>
    @php
        $routeName = Route::currentRouteName();
        $title = match ($routeName) {
            'jadwal.create' => 'Tambah Data Jadwal Pelajaran',
            'jadwal.edit' => 'Ubah Data Jadwal Pelajaran',
            default => 'Data Jadwal Pelajaran',
        };
    @endphp

    <div class="dash-card">
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        <form class="flex flex-col gap-3 max-w-[768px]">
            <flux:field>
                <flux:label>Nama Mata Pelajaran</flux:label>
                <x-select name="mapel" wireModel="mapel_id" :options="App\Models\MataPelajaran::all()
                    ->map(fn($g) => ['value' => $g->id, 'label' => $g->nama_mapel])
                    ->toArray()" placeholder="Pilih mata pelajaran..." />
                <flux:error name="mapel" />
            </flux:field>

            <flux:field>
                <flux:label>Kelas</flux:label>
                <x-select name="kelas" wireModel="kelas_id" :options="App\Models\Kelas::all()
                    ->map(fn($g) => ['value' => $g->id, 'label' => $g->nama_kelas])
                    ->toArray()" placeholder="Pilih kelas..." />
                <flux:error name="kelas" />
            </flux:field>

            <flux:field>
                <flux:label>Hari</flux:label>

                @php
                    $hariOptions = collect(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])
                        ->map(fn($hari) => ['label' => $hari, 'value' => $hari])
                        ->toArray();
                @endphp

                <x-select name="hari" wireModel="hari" :search="false" :options="$hariOptions"
                    placeholder="Pilih hari..." />
                <flux:error name="hari" />
            </flux:field>

            <div class="flex flex-row items-center gap-6 w-full">
                <flux:field class="min-w-[200px]">
                    <flux:label>Jam Mulai</flux:label>
                    <x-time-picker class="w-full" />
                    <flux:error name="jam_mulai" />
                </flux:field>

                <flux:field class="min-w-[200px]">
                    <flux:label>Jam Selesai</flux:label>
                    <x-time-picker class="w-full" />
                    <flux:error name="jam_selesai" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Guru Pengajar</flux:label>
                <x-select name="guru_pengajar" wireModel="guru_id" :options="App\Models\Guru::all()
                    ->map(fn($g) => ['value' => $g->id, 'label' => $g->nama_guru])
                    ->toArray()" placeholder="Pilih guru..." />
                <flux:error name="guru_pengajar" />
            </flux:field>

            <flux:button type="submit" variant="filled" class="!bg-primary w-fit !text-white mt-8 !py-6 !px-10">Simpan
            </flux:button>
        </form>
    </div>
</div>
