<?php

use App\Imports\GuruImport;
use App\Imports\JadwalPelajaranImport;
use App\Imports\KelasImport;
use App\Imports\MapelImport;
use App\Models\ActivityLog;
use Filament\Notifications\Notification;
use Livewire\Component;
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
            'kelas' => $this->importKelas($path),
            default => throw new \Exception('Context tidak dikenal'),
        };
        Flux::modal('import-excel')->close();
    }

    private function importGuru($path)
    {
        $guru = new GuruImport();
        ActivityLog::withoutLogs(function () use ($guru, $path) {
            $guru->import($path);
        });
        $errors = $guru->errors();

        if (count($errors) >= 1) {
            Notification::make()->title('Terjadi error saat import data')->danger()->persistent()->send();
            return;
        }

        ActivityLog::record(
            action: 'create',
            description: 'Import data Guru dari berkas Excel',
            module: 'Guru'
        );

        Notification::make()->title('Data guru berhasil di unggah!')->success()->send();
        $this->dispatch('refreshGuruTable');
    }

    private function importKelas($path)
    {
        try {
            ActivityLog::withoutLogs(function () use ($path) {
                Excel::import(new KelasImport(), $path);
            });

            ActivityLog::record(
                action: 'create',
                description: 'Import data Kelas dari berkas Excel',
                module: 'Kelas'
            );

            Notification::make()->title('Data Kelas berhasil di unggah!')->success()->send();
            $this->dispatch('refreshTable');
        } catch (\Throwable $th) {
            Notification::make()->title('Terjadi error saat import data')->body($th->getMessage())->danger()->persistent()->send();
        }
    }

    private function importMapel($path)
    {
        try {
            ActivityLog::withoutLogs(function () use ($path) {
                Excel::import(new MapelImport(), $path);
            });

            ActivityLog::record(
                action: 'create',
                description: 'Import data Mata Pelajaran dari berkas Excel',
                module: 'Mata Pelajaran'
            );

            Notification::make()->title('Data Mata Pelajaran berhasil di unggah!')->success()->send();
            $this->dispatch('refreshMapelTable');
        } catch (\Throwable $th) {
            Notification::make()->title('Terjadi error saat import data')->body($th->getMessage())->danger()->persistent()->send();
        }
    }

    private function importJadwal($path)
    {
        try {
            $importedCount = 0;
            $bentrokList = [];
            ActivityLog::withoutLogs(function () use ($path, &$importedCount, &$bentrokList) {
                $mapel = new JadwalPelajaranImport($this->periodeId);
                $mapel->import($path);
                $importedCount = $mapel->getImportedCount();
                $bentrokList = $mapel->getBentrokList();
            });

            ActivityLog::record(
                action: 'create',
                description: "Import data Jadwal Pelajaran dari berkas Excel ({$importedCount} data)",
                module: 'Jadwal Pelajaran'
            );

            if (count($bentrokList) > 0) {
                Notification::make()
                    ->title("Import Selesai ({$importedCount} data), tetapi terdapat " . count($bentrokList) . " bentrok!")
                    ->warning()
                    ->persistent()
                    ->send();

                $this->dispatch('openBentrokSummaryModal', bentrokList: $bentrokList);
            } else {
                Notification::make()
                    ->title('Jadwal Pelajaran berhasil di unggah!')
                    ->body("Total data yang diimport: {$importedCount}")
                    ->success()
                    ->send();
            }

            $this->dispatch('refreshJadwalTable');
        } catch (\Throwable $th) {
            Notification::make()->title('Terjadi error saat import data')->body($th->getMessage())->danger()->persistent()->send();
        }
    }
}; ?>

<div x-data="{ isUploadingFile: false }"
    x-on:file-uploading.window="isUploadingFile = true"
    x-on:file-uploaded.window="isUploadingFile = false"
    x-on:file-upload-error.window="isUploadingFile = false">
    <flux:modal name="{{ $name }}" class="w-[90%] md:w-[32rem]">
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
                    <button type="submit"
                        :disabled="isUploadingFile"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="px-4 py-2 text-sm font-semibold rounded-lg bg-primary text-white hover:bg-primary/90 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span wire:loading.remove wire:target="save" x-show="!isUploadingFile">Unggah Data</span>
                        <span x-show="isUploadingFile" x-cloak>Mengunggah File...</span>
                        <span wire:loading wire:target="save" x-cloak>Memproses Import...</span>
                    </button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>