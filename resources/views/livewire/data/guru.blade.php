<?php

use Livewire\Attributes\Validate;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component {
    use WithFileUploads;

    #[Validate('file|mimes:csv,excel:max:1024')]
    public $excelFile;

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
                <flux:button icon="document" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            </flux:modal.trigger>

            <flux:modal.trigger name="add-data">
                <flux:button icon="plus" class="!bg-primary !text-white">Tambah Data</flux:button>
            </flux:modal.trigger>

            <flux:button icon="arrow-down-tray">Unduh Data</flux:button>
        </x-slot>
    </x-card-heading>

    <livewire:datatable.index :columns="$this->columnDefs" />

    <!-- Import Excel Modal -->
    <flux:modal name="import-excel" class="min-w-[22rem]">
    <div class="space-y-6">
        <div class="space-y-3">
            <flux:heading size="lg">Import Data</flux:heading>
            <flux:badge color="amber" class="whitespace-normal">Silakan unduh file template <flux:link href="#" class="mx-1 text-amber-800 font-bold">Excel Data Guru</flux:link> untuk melakukan import data.</flux:badge>
        </div>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="filled" class="!bg-primary !text-white disabled:text-gray-700 !disabled:bg-primary/40">Simpan</flux:button>
            </div>
    </div>
</flux:modal>

<!-- Add Data Modal -->
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
