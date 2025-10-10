<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $title = 'Unggah Berkas';
    public array $fileTypes = ['.xlsx', '.csv'];
    public int $maxSize = 1024; // KB (default 1 MB)
    public $file;

    #[On('trigger-upload')]
    public function save()
    {
        $this->validate([
            'file' => 'required|file|mimes:' . $this->getMimes() . '|max:' . $this->maxSize, // 2MB max
        ]);

        $path = $this->file->store('uploads');
        // $path = "test-file.xlsx";

        // kasih event sukses
        $this->dispatch('upload-success', path: $path);

        session()->flash('success', "Berkas berhasil diunggah");
        $this->reset('file');
    }

    private function getMimes(): string
    {
        // convert dari ".pdf", ".jpg" → "pdf,jpg"
        return collect($this->fileTypes)
            ->map(fn($type) => ltrim($type, '.'))
            ->implode(',');
    }
};
?>

<div>
    <!-- <label for="file" class="block text-sm text-gray-500 dark:text-gray-300">File</label> -->

    <label for="dropzone-file"
        class="flex flex-col items-center w-full max-w-lg p-5 mx-auto mt-2 text-center bg-white border-2 border-gray-300 border-dashed cursor-pointer dark:bg-gray-900 dark:border-gray-700 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-8 h-8 text-gray-500 dark:text-gray-400">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
        </svg>

        <h2 class="mt-1 font-medium tracking-wide text-gray-700 dark:text-gray-200">{{ $title }}</h2>

        <p class="mt-2 text-xs tracking-wide text-gray-500 dark:text-gray-400">
            Klik untuk memilih berkas. Format yang diperbolehkan:
            {{ implode(', ', $fileTypes) }}
        </p>

        <input id="dropzone-file" type="file" wire:model="file"
            accept="{{ implode(',', $fileTypes) }}" class="hidden" />
    </label>

    {{-- Preview nama file --}}
    @if ($file)
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            File dipilih: <span class="font-semibold">{{ $file->getClientOriginalName() }}</span>
        </p>
    @endif

    {{-- Error --}}
    @error('file')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror

    {{-- Progress bar --}}
    <div wire:loading wire:target="file" class="mt-3">
        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
            <div class="bg-blue-600 h-2.5 rounded-full animate-pulse" style="width: 50%"></div>
        </div>
        <p class="text-xs text-gray-500 mt-1">Mengunggah...</p>
    </div>

    <!-- <div class="mt-4">
        <button wire:click="save" wire:loading.attr="disabled"
            class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50">
            Upload
        </button>
    </div> -->

    @if (session()->has('success'))
        <p class="mt-2 text-green-600 text-sm">{{ session('success') }}</p>
    @endif
</div>
