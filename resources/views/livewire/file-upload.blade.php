<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $title = 'Unggah Berkas';
    public array $fileTypes = ['.xlsx', '.csv', '.xls'];
    public int $maxSize = 10240; // 10MB
    public $file;

    public function updatedFile()
    {
        $this->resetErrorBag();
        $this->validate([
            'file' => 'required|file|max:' . $this->maxSize,
        ], [
            'file.required' => 'Silakan pilih berkas Excel terlebih dahulu.',
            'file.file' => 'Berkas yang diunggah tidak valid.',
            'file.max' => 'Ukuran berkas maksimal 10MB.',
        ]);
        $this->dispatch('file-ready');
    }

    #[On('reset-file-upload')]
    public function resetFile()
    {
        $this->reset('file');
        $this->resetErrorBag();
    }

    #[On('trigger-upload')]
    public function save()
    {
        $this->resetErrorBag();

        if (!$this->file) {
            $this->addError('file', 'Silakan pilih berkas Excel terlebih dahulu.');
            $this->dispatch('upload-failed');
            return;
        }

        $this->validate([
            'file' => 'required|file|max:' . $this->maxSize,
        ], [
            'file.required' => 'Silakan pilih berkas Excel terlebih dahulu.',
            'file.file' => 'Berkas yang diunggah tidak valid.',
            'file.max' => 'Ukuran berkas maksimal 10MB.',
        ]);

        $path = $this->file->store('uploads');

        $this->dispatch('upload-success', path: $path);

        session()->flash('success', "Berkas berhasil diunggah");
        $this->reset('file');
    }
};
?>

<div x-data="{ uploading: false, progress: 0 }"
    x-on:livewire-upload-start="uploading = true; $dispatch('file-uploading')"
    x-on:livewire-upload-finish="uploading = false; $dispatch('file-uploaded')"
    x-on:livewire-upload-error="uploading = false; $dispatch('file-upload-error')"
    x-on:livewire-upload-progress="progress = $event.detail.progress">

    <label for="dropzone-file"
        class="flex flex-col items-center w-full max-w-lg p-5 mx-auto mt-2 text-center bg-white border-2 border-gray-300 border-dashed cursor-pointer dark:bg-gray-900 dark:border-gray-700 rounded-xl hover:border-primary transition relative overflow-hidden"
        :class="{ 'border-primary bg-primary/5': uploading }">

        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="w-8 h-8 text-gray-500 dark:text-gray-400" x-show="!uploading">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
        </svg>

        <div x-show="uploading" class="w-8 h-8 border-3 border-primary border-t-transparent rounded-full animate-spin"></div>

        <h2 class="mt-1 font-medium tracking-wide text-gray-700 dark:text-gray-200" x-show="!uploading">{{ $title }}</h2>
        <h2 class="mt-1 font-medium tracking-wide text-primary" x-show="uploading" x-cloak>Mengunggah berkas ke server...</h2>

        <p class="mt-2 text-xs tracking-wide text-gray-500 dark:text-gray-400" x-show="!uploading">
            Klik untuk memilih berkas. Format yang diperbolehkan:
            {{ implode(', ', $fileTypes) }}
        </p>

        <input id="dropzone-file" type="file" wire:model.live="file" accept="{{ implode(',', $fileTypes) }}"
            class="hidden" />
    </label>

    {{-- Progress bar --}}
    <div x-show="uploading" class="mt-3 space-y-1" x-cloak>
        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
            <div class="bg-primary h-2 rounded-full transition-all duration-300" :style="`width: ${progress}%`"></div>
        </div>
        <div class="flex justify-between text-[11px] text-gray-500">
            <span>Mengunggah...</span>
            <span x-text="`${progress}%`"></span>
        </div>
    </div>

    {{-- Preview nama file --}}
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

    {{-- Error --}}
    @error('file')
        <div class="mt-2 text-red-500 text-xs font-semibold flex items-center gap-1">
            <flux:icon name="exclamation-circle" class="w-3.5 h-3.5" />
            <span>{{ $message }}</span>
        </div>
    @enderror

    @if (session()->has('success'))
        <p class="mt-2 text-green-600 text-xs font-semibold">{{ session('success') }}</p>
    @endif
</div>