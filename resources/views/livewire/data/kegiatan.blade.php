<?php

use App\Models\Kegiatan;
use Filament\Forms\Components\ColorPicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Title('Data Kegiatan')]
class extends Component implements HasSchemas {
    use InteractsWithSchemas;

    public ?array $formData = [
        'kode_kegiatan' => '',
        'nama_kegiatan' => '',
        'warna' => '',
    ];
    public bool $isEdit = false;

    protected function rules(): array
    {
        return [
            'formData.kode_kegiatan' => 'required|string|max:12',
            'formData.nama_kegiatan' => 'required|string|max:40',
            'formData.hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.kode_kegiatan.required' => 'Nama kegiatan wajib diisi.',
            'formData.kode_kegiatan.string' => 'Nama kegiatan harus berupa teks.',
            'formData.kode_kegiatan.max' => 'Nama kegiatan tidak boleh lebih dari 12 karakter.',

            'formData.nama_kegiatan.required' => 'Nama kegiatan wajib diisi.',
            'formData.nama_kegiatan.string' => 'Nama kegiatan harus berupa teks.',
            'formData.nama_kegiatan.max' => 'Nama kegiatan tidak boleh lebih dari 40 karakter.',

            'formData.hari.required' => 'Hari wajib diisi.',
            'formData.hari.in' => 'Hari harus salah satu dari Senin sampai Minggu.',
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ColorPicker::make('formData.warna')->label('Warna')->placeholder('Pilih warna untuk jadwal')->regex('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})\b$/')
            ]);
    }

    public function openAddJadwalModal()
    {
        $this->isEdit = false;
        $this->formData = [
            'kode_kegiatan' => '',
            'nama_kegiatan' => '',
            'warna' => '',
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
        // $this->validate();

        if ($this->isEdit) {
            Kegiatan::find($this->formData['id'])->update($this->formData);
        } else {
            Kegiatan::create($this->formData);
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
    <x-card-heading title="Data Kegiatan">
        <x-slot name="action_buttons">
            <flux:button icon="plus" @click="$wire.openAddJadwalModal" class="!bg-primary !text-white">
                Tambah Data</flux:button>
        </x-slot>
    </x-card-heading>

    <livewire:datatable.kegiatan />

    {{-- Add Data Modal --}}
    <flux:modal name="jadwal-modal" class="md:w-[720px]">
        <form wire:submit.prevent="save" class="flex flex-col gap-4 max-w-[768px]">
            <flux:heading size="lg">
                {{ $isEdit ? 'Ubah Data' : 'Tambah Data' }} Kegiatan
            </flux:heading>

            <flux:input wire:model.defer="formData.kode_kegiatan" label="Kode Kegiatan" placeholder="Kode Kegiatan" />
            <flux:input wire:model.defer="formData.nama_kegiatan" label="Nama Kegiatan" placeholder="Nama Kegiatan" />

            {{-- Filament form --}}
            {{ $this->form }}

            <div class="flex mt-8">
                <flux:spacer />
                <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
