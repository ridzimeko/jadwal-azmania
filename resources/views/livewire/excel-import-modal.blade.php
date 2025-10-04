<?php

use Livewire\Volt\Component;
use Flux\Flux;

new class extends Component {
    public $context;
    public $name = 'import-excel';

    public function save()
    {
        $this->dispatch('trigger-upload');
    }

    public function onUploadSuccess($path)
    {
        match ($this->context) {
            'guru' => $this->importGuru($path),
            'jadwal' => $this->importJadwal($path),
            default => throw new \Exception("Context tidak dikenal"),
        };
        Flux::modal('import-excel')->close();
    }

    private function importGuru($path) { /* logic insert guru */ }
    private function importJadwal($path) { /* logic insert jadwal */ }
}; ?>

<flux:modal name="{{ $name }}" class="min-w-[22rem]">
    <form wire:submit.prevent="save">
        <div class="space-y-6">
            <div class="space-y-1 mb-6">
                <flux:heading size="lg">Import Data</flux:heading>
                <flux:text class="whitespace-normal">Silakan unduh berkas
                    <flux:badge as="button" color="green" icon="file-excel" size="sm">Template Excel</flux:badge> untuk melakukan import data.
                </flux:text>
            </div>
            <livewire:file-upload title="Import Data Excel" @upload-success="onUploadSuccess" />
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="filled"
                    class="!bg-primary !text-white disabled:text-gray-700 !disabled:bg-primary/40">Unggah data
                </flux:button>
            </div>
        </div>
    </form>
</flux:modal>
