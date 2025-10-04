<?php

use Flux\Flux;
use Livewire\Volt\Component;

new class extends Component {
    protected  $columnDefs = [
        ['name' => 'No', 'field' => 'no'],
        ['name' => 'Kode Kelas', 'field' => 'kode_kelas'],
        ['name' => 'Kelas', 'field' => 'kelas'],
    ];
};
?>

<div class="dash-card">
    <x-card-heading title="Data Kelas">
        <x-slot name="action_buttons">
            <flux:modal.trigger name="add-data">
                <flux:button icon="plus" class="!bg-primary !text-white">Tambah Data</flux:button>
            </flux:modal.trigger>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index :columns="$this->columnDefs" />

    {{-- Add Data Modal --}}
    <flux:modal name="add-data" class="md:w-96">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Tambah Data Kelas</flux:heading>
            </div>
            <flux:input label="Kode Kelas" placeholder="Kode Kelas" />
            <flux:input label="Nama Kelas" placeholder="Nama Kelas" />
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
