<?php

use App\Models\JamPelajaran;
use Filament\Notifications\Notification;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Data Kelas')] class extends Component {
    protected $columnDefs = [['name' => 'Urutan', 'field' => 'urutan'], ['name' => 'Jam Mulai', 'field' => 'jam_mulai'], ['name' => 'Jam Selesai', 'field' => 'jam_selesai']];

    public ?array $formData = null;
    public bool $isEdit = false;

    protected function rules(): array
    {
        return [
            'formData.urutan' => ['required', 'integer', 'max:12', 'unique:jam_pelajaran,urutan'],
            'formData.jam_mulai' => 'required|date_format:H:i',
            'formData.jam_selesai' => 'required|date_format:H:i|after:formData.jam_mulai',
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.urutan.required' => 'Urutan wajib diisi.',
            'formData.urutan.string' => 'Urutan harus berupa teks.',
            'formData.urutan.max' => 'Urutan tidak boleh lebih dari 12 karakter.',
            'formData.urutan.unique' => 'Urutan sudah terdaftar, gunakan kode lain.',

            'formData.jam_mulai.required' => 'Jam mulai wajib diisi.',
            'formData.jam_mulai.date_format' => 'Format jam mulai harus HH:MM (24 jam).',

            'formData.jam_selesai.required' => 'Jam selesai wajib diisi.',
            'formData.jam_selesai.date_format' => 'Format jam selesai harus HH:MM (24 jam).',
            'formData.jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ];
    }

    #[On('openAddModal')]
    public function openAddModal()
    {
        $this->isEdit = false;
        $this->formData = [
            'urutan' => '',
            'jam_mulai' => '',
            'jam_selesai' => '',
        ];
        Flux::modal('jam-modal')->show();
    }

    #[On('openEditModal')]
    public function openEditModal($record)
    {
        $this->isEdit = true;
        $this->formData = $record;
        Flux::modal('jam-modal')->show();
    }

    public function save()
    {
        $this->validate();

        if ($this->isEdit) {
            JamPelajaran::find($this->formData['id'])->update([
                'urutan' => $this->formData['urutan'],
                'jam_mulai' => $this->formData['jam_mulai'],
                'jam_selesai' => $this->formData['jam_selesai'],
            ]);
        } else {
            JamPelajaran::create($this->formData);
        }

        Notification::make()->title('Data Jam Pelajaran Tersimpan')->success()->send();
        Flux::modal('jam-modal')->close();
        $this->dispatch('refreshTable');
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Data Jam Pelajaran">
        <x-slot name="action_buttons">
            <flux:button wire:click="openAddModal" icon="plus" class="!bg-primary !text-white">Tambah Data
            </flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index actionType="data" :columns="$this->columnDefs" :model="JamPelajaran::class" />

    {{-- Add Data Modal --}}
    <flux:modal name="jam-modal" class="w-[85%] md:w-[480px]">
        <form wire:submit.prevent="save">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">
                        {{ $isEdit ? 'Ubah Data' : 'Tambah Data' }} Jam Pelajaran
                    </flux:heading>
                </div>
                <flux:input wire:model.defer="formData.urutan" label="Urutan Jam" placeholder="Urutan Jam" />

                <div class="flex flex-row items-center gap-6 w-full">
                    <flux:field class="min-w-[200px]">
                        <flux:label>Jam Mulai</flux:label>
                        <x-time-picker name="formData.jam_mulai" wire:model.defer="formData.jam_mulai" class="w-full" />
                        <flux:error name="formData.jam_mulai" />
                    </flux:field>

                    <flux:field class="min-w-[200px]">
                        <flux:label>Jam Selesai</flux:label>
                        <x-time-picker name="formData.jam_selesai" wire:model.defer="formData.jam_selesai" class="w-full" />
                        <flux:error name="formData.jam_selesai" />
                    </flux:field>
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    {{-- Import Excel Modal --}}
    <livewire:excel-import-modal context="kelas" />
</div>