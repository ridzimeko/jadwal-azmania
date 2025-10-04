<?php

use Flux\Flux;
use Livewire\Volt\Component;

new class extends Component {
    protected $columnDefs = [
        ['name' => 'No', 'field' => 'no'],
        ['name' => 'NIP', 'field' => 'nip'],
        ['name' => 'Nama Guru', 'field' => 'guru']
    ];
};
?>

<div class="dash-card">
    <x-card-heading title="Data Guru">
        <x-slot name="action_buttons">
            <flux:modal.trigger name="import-excel">
                <flux:button icon="file-excel" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            </flux:modal.trigger>
            <flux:modal.trigger name="add-data">
                <flux:button icon="plus" class="!bg-primary !text-white">Tambah Data</flux:button>
            </flux:modal.trigger>
            <flux:button icon="arrow-down-tray">Unduh Data</flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index :columns="$this->columnDefs" />

    {{-- Import Excel Modal --}}
    <livewire:excel-import-modal context="guru" />

    {{-- Add Data Modal --}}
    <flux:modal name="add-data" class="md:w-96">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Tambah Data Guru</flux:heading>
            </div>
            <flux:input label="NIP" placeholder="NIP" />
            <flux:input label="Nama Guru" placeholder="Nama Guru" />
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
