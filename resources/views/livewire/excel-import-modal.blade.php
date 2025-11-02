<?php

use App\Imports\GuruImport;
use App\Imports\JadwalPelajaranImport;
use App\Imports\MapelImport;
use Filament\Notifications\Notification;
use Livewire\Volt\Component;
use Flux\Flux;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    public $context;
    public $name = 'import-excel';
    public $periodeId;

    public function save()
    {
        $this->dispatch('trigger-upload');
    }

    public function onUploadSuccess($path)
    {
        match ($this->context) {
            'guru' => $this->importGuru($path),
            'jadwal' => $this->importJadwal($path),
            'mapel' => $this->importMapel($path),
            default => throw new \Exception('Context tidak dikenal'),
        };
        Flux::modal('import-excel')->close();
    }

    private function importGuru($path)
    {
        $guru = new GuruImport();
        $guru->import($path);
        $errors = $guru->errors();

        if (count($errors) >= 1) {
            Notification::make()->title('Terjadi error saat import data')->danger()->persistent()->send();
            return;
        }

        Notification::make()->title('Data guru berhasil di unggah!')->success()->send();
        $this->dispatch('refreshTable');
    }

    private function importMapel($path)
    {
        try {
        Excel::import(new MapelImport(), $path);
            Notification::make()->title('Data Mata Pelajaran berhasil di unggah!')->success()->send();
            $this->dispatch('refreshMapelTable');
        } catch (\Throwable $th) {
            Notification::make()->title('Terjadi error saat import data')->body($th->getMessage())->danger()->persistent()->send();
        }
    }

    private function importJadwal($path)
    {
        try {
            $mapel = new JadwalPelajaranImport($this->periodeId);
            $mapel->import($path);

            Notification::make()
                ->title('Jadwal Pelajaran berhasil di unggah!')
                ->body("Total data yang diimport: {$mapel->getImportedCount()}")
                ->success()
                ->send();
            $this->dispatch('refreshJadwalTable');
        } catch (\Throwable $th) {
            Notification::make()->title('Terjadi error saat import data')->body($th->getMessage())->danger()->persistent()->send();
        }
    }
}; ?>

<div>
    <flux:modal name="{{ $name }}" class="min-w-[22rem]">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div class="space-y-1 mb-6">
                    <flux:heading size="lg">Import Data</flux:heading>
                    <flux:text class="whitespace-normal">Silakan unduh berkas
                        <flux:badge as="button"
                            x-on:click="window.location.href='{{ route('download.template', $this->context) }}'"
                            color="green" icon="file-excel" size="sm">Template Excel
                        </flux:badge> untuk melakukan import data.
                    </flux:text>
                </div>
                <livewire:file-upload title="Import Data Excel" @upload-success="onUploadSuccess($event.detail.path)" />
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="filled"
                        class="!bg-primary !text-white disabled:text-gray-700 !disabled:bg-primary/40">Unggah Data
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
