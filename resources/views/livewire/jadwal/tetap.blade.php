<?php

use Filament\Notifications\Notification;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Title('Jadwal Tetap')]
class extends Component {
    public ?array $formData = [
        'nama' => '',
        'jam_mulai' => '',
        'jam_selesai' => '',
    ];
    public bool $isEdit = false;

    protected function rules(): array
    {
        return [
            'formData.nama' => 'required|string|max:40',
            'formData.hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'formData.jam_mulai' => 'required|date_format:H:i',
            'formData.jam_selesai' => 'required|date_format:H:i|after:formData.jam_mulai',
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.nama.required' => 'Nama kegiatan wajib diisi.',
            'formData.nama.string' => 'Nama kegiatan harus berupa teks.',
            'formData.nama.max' => 'Nama kegiatan tidak boleh lebih dari 40 karakter.',

            'formData.hari.required' => 'Hari wajib diisi.',
            'formData.hari.in' => 'Hari harus salah satu dari Senin sampai Minggu.',

            'formData.jam_mulai.required' => 'Jam mulai wajib diisi.',
            'formData.jam_mulai.date_format' => 'Format jam mulai harus HH:MM (24 jam).',

            'formData.jam_selesai.required' => 'Jam selesai wajib diisi.',
            'formData.jam_selesai.date_format' => 'Format jam selesai harus HH:MM (24 jam).',
            'formData.jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ];
    }

    public function openAddJadwalModal()
    {
        $this->isEdit = false;
        $this->formData = [
            'nama' => '',
            'jam_mulai' => '',
            'jam_selesai' => '',
        ];
        Flux::modal('jadwal-modal')->show();
    }

    #[On('openEditJadwal')]
    public function openEditJadwal($record)
    {
        if ($record['id'] ?? null) {
            $this->isEdit = true;
        }
        $this->formData = $record;
        Flux::modal('jadwal-modal')->show();
    }

    public function save()
    {
        $this->validate();

        if ($this->isEdit) {
            \App\Models\JadwalTetap::find($this->formData['id'])->update($this->formData);
        } else {
            \App\Models\JadwalTetap::create($this->formData);
        }

        Notification::make()
            ->title('Jadwal Tetap Berhasil Tersimpan')
            ->success()
            ->send();
        Flux::modal('jadwal-modal')->close();
        $this->dispatch('refreshJadwalTetapTable');
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Jadwal Tetap"
        description="Manajemen jadwal tetap pelajaran">
        <x-slot name="action_buttons">
            <flux:button icon="plus" @click="$wire.openAddJadwalModal" class="!bg-primary !text-white">
                Tambah Data</flux:button>
        </x-slot>
    </x-card-heading>

    <livewire:datatable.jadwal-tetap />

    {{-- Add Data Modal --}}
    <flux:modal name="kegiatan-nonkbm-modal" class="md:w-[720px]">
        <form wire:submit.prevent="save" class="flex flex-col gap-3 max-w-[768px]">
            <flux:heading size="lg">
                {{ $isEdit ? 'Ubah Data Jadwal' : 'Tambah Data Jadwal' }}
            </flux:heading>

            <flux:input wire:model.defer="formData.nama" label="Nama Kegiatan" placeholder="Nama Kegiatan" />

            <div class="flex flex-row items-center gap-6 w-full">
                <flux:field class="min-w-[200px]">
                    <flux:label>Jam Mulai</flux:label>
                    <x-time-picker name="formData.jam_mulai" wire:model="formData.jam_mulai" class="w-full" />
                    <flux:error name="formData.jam_mulai" />
                </flux:field>

                <flux:field class="min-w-[200px]">
                    <flux:label>Jam Selesai</flux:label>
                    <x-time-picker name="formData.jam_selesai" wire:model="formData.jam_selesai" class="w-full" />
                    <flux:error name="formData.jam_selesai" />
                </flux:field>
            </div>

            <div class="flex mt-8">
                <flux:spacer />
                <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
