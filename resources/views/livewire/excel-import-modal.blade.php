<?php

use App\Imports\GuruImport;
use App\Imports\JadwalPelajaranImport;
use App\Imports\KelasImport;
use App\Imports\MapelImport;
use App\Models\ActivityLog;
use Filament\Notifications\Notification;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithFileUploads;

    public $context;
    public $name = 'import-excel';
    public $periodeId;
    public $file;

    public function resetFile()
    {
        $this->reset('file');
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->resetErrorBag();

        $this->validate([
            'file' => 'required|file|max:10240',
        ], [
            'file.required' => 'Silakan pilih berkas Excel terlebih dahulu.',
            'file.file' => 'Berkas yang diunggah tidak valid.',
            'file.max' => 'Ukuran berkas maksimal 10MB.',
        ]);

        $path = $this->file->store('uploads');
        $this->sanitizeUploadedExcel($path);

        if (class_exists(\Barryvdh\Debugbar\Facades\Debugbar::class)) {
            \Barryvdh\Debugbar\Facades\Debugbar::disable();
        }

        switch ($this->context) {
            case 'guru':
                $this->importGuru($path);
                break;
            case 'jadwal':
                $this->importJadwal($path);
                break;
            case 'mapel':
                $this->importMapel($path);
                break;
            case 'kelas':
                $this->importKelas($path);
                break;
            default:
                throw new \Exception('Context tidak dikenal');
        }

        $this->reset('file');
        Flux::modal($this->name)->close();
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

    private function sanitizeUploadedExcel(string $relativePath): void
    {
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($relativePath);
        if (!file_exists($fullPath) || !str_ends_with(strtolower($fullPath), '.xlsx')) {
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($fullPath) === true) {
            $hasModified = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#', $filename)) {
                    $xml = $zip->getFromIndex($i);
                    if (str_contains($xml, '1638')) {
                        $pattern = '#<col[^>]+1638[0-9][^>]*\\/?' . chr(62) . '#i';
                        $xmlClean = preg_replace($pattern, '', $xml);
                        $zip->deleteIndex($i);
                        $zip->addFromString($filename, $xmlClean);
                        $hasModified = true;
                    }
                }
            }
            $zip->close();
        }
    }
}; ?>

<div x-data="{ isUploading: false, progress: 0 }"
    x-on:livewire-upload-start="isUploading = true"
    x-on:livewire-upload-finish="isUploading = false"
    x-on:livewire-upload-error="isUploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress">
    <flux:modal name="{{ $name }}" class="w-[90%] md:w-[32rem]">
        <form wire:submit.prevent="save">
            <div class="space-y-6">
                <div class="space-y-1 mb-4">
                    <flux:heading size="lg">Import Data</flux:heading>
                    <flux:text class="whitespace-normal">Silakan unduh berkas
                        <flux:badge as="button"
                            x-on:click="window.location.href='{{ route('download.template', $this->context) }}'"
                            color="green" icon="file-excel" size="sm">Template Excel
                        </flux:badge> untuk melakukan import data.
                    </flux:text>
                </div>

                {{-- Dropzone File Input --}}
                <div>
                    <label for="dropzone-file-{{ $context }}"
                        class="flex flex-col items-center w-full p-5 text-center bg-white border-2 border-gray-300 border-dashed cursor-pointer dark:bg-gray-900 dark:border-gray-700 rounded-xl hover:border-primary transition relative overflow-hidden"
                        :class="{ 'border-primary bg-primary/5': isUploading }">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                            class="w-8 h-8 text-gray-500 dark:text-gray-400" x-show="!isUploading">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                        </svg>

                        <div x-show="isUploading" class="w-8 h-8 border-3 border-primary border-t-transparent rounded-full animate-spin"></div>

                        <h2 class="mt-1 font-medium tracking-wide text-gray-700 dark:text-gray-200" x-show="!isUploading">Import Data Excel</h2>
                        <h2 class="mt-1 font-medium tracking-wide text-primary" x-show="isUploading" x-cloak>Mengunggah berkas ke server...</h2>

                        <p class="mt-2 text-xs tracking-wide text-gray-500 dark:text-gray-400" x-show="!isUploading">
                            Klik untuk memilih berkas. Format: .xlsx, .xls, .csv (Maks. 10MB)
                        </p>

                        <input id="dropzone-file-{{ $context }}" type="file" wire:model.live="file" accept=".xlsx,.xls,.csv"
                            class="hidden" />
                    </label>

                    {{-- Progress Bar --}}
                    <div x-show="isUploading" class="mt-3 space-y-1" x-cloak>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                            <div class="bg-primary h-2 rounded-full transition-all duration-300" :style="`width: ${progress}%`"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-gray-500">
                            <span>Mengunggah...</span>
                            <span x-text="`${progress}%`"></span>
                        </div>
                    </div>

                    {{-- File Preview --}}
                    @if ($file)
                        <div class="mt-2 flex items-center justify-between p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800">
                            <div class="flex items-center gap-2 text-xs text-emerald-800 dark:text-emerald-300 font-medium overflow-hidden">
                                <flux:icon name="document-text" class="w-4 h-4 text-emerald-600 shrink-0" />
                                <span class="truncate">{{ $file->getClientOriginalName() }}</span>
                            </div>
                            <button type="button" wire:click="resetFile" class="text-xs text-red-500 hover:text-red-700 font-semibold shrink-0">
                                Hapus
                            </button>
                        </div>
                    @endif

                    {{-- Error Message --}}
                    @error('file')
                        <div class="mt-2 text-red-500 text-xs font-semibold flex items-center gap-1">
                            <flux:icon name="exclamation-circle" class="w-3.5 h-3.5" />
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost" wire:click="resetFile">Batal</flux:button>
                    </flux:modal.close>
                    <button type="submit"
                        :disabled="isUploading"
                        wire:loading.attr="disabled"
                        wire:target="save,file"
                        class="px-4 py-2 text-sm font-semibold rounded-lg bg-primary text-white hover:bg-primary/90 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span wire:loading.remove wire:target="save,file">Unggah Data</span>
                        <span wire:loading wire:target="file" x-cloak>Mengunggah Berkas...</span>
                        <span wire:loading wire:target="save" x-cloak>Memproses Import...</span>
                    </button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>