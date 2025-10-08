<?php

use Livewire\Volt\Component;

new class extends Component {
    protected $columnDefs = [
        // ['name' => 'No', 'field' => 'no'],
        ['name' => 'Kelas', 'field' => 'kelas_nama'],
        ['name' => 'Hari', 'field' => 'hari'],
        ['name' => 'Jam Mulai', 'field' => 'jam_mulai'],
        ['name' => 'Jam Selesai', 'field' => 'jam_selesai'],
        ['name' => 'Mata Pelajaran', 'field' => 'mapel_nama'],
        ['name' => 'Guru Pengajar', 'field' => 'guru_nama'],
    ];

    public $tingkat;

    public function mount($tingkat)
    {
        $this->tingkat = $tingkat;
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Jadwal Pelajaran {{ strtoupper($tingkat) }}" description="Manajemen jadwal Pelajaran untuk tingkat {{ strtoupper($tingkat) }}">
        <x-slot name="action_buttons">
            <flux:modal.trigger name="import-excel">
                <flux:button icon="file-excel" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            </flux:modal.trigger>
            <flux:button icon="plus" :href="route('jadwal.edit', $this->tingkat)" class="!bg-primary !text-white">Tambah Data</flux:button>
            <flux:button icon="arrow-down-tray">Unduh Data</flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index :columns="$this->columnDefs" :model="\App\Models\JadwalPelajaran::class" />

    {{-- Import Excel Modal --}}
    <livewire:excel-import-modal context="jadwal{{ strtoupper($tingkat) }}" />
</div>
