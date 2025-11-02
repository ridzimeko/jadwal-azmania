<?php

use Filament\Notifications\Notification;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Title('Data Kelas')]
class extends Component {
    protected $columnDefs = [['name' => 'Kode Kelas', 'field' => 'kode_kelas'], ['name' => 'Tingkat', 'field' => 'tingkat'], ['name' => 'Kelas', 'field' => 'nama_kelas']];

    public ?array $formData = null;
    public bool $isEdit = false;

    protected function rules(): array
    {
        return [
            'formData.kode_kelas' => ['required', 'string', 'max:12', Rule::unique('kelas', 'kode_kelas')->ignore($this->formData['id'] ?? null)],
            'formData.nama_kelas' => ['required', 'string', 'max:15'],
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.kode_kelas.required' => 'Kode kelas wajib diisi.',
            'formData.kode_kelas.string' => 'Kode kelas harus berupa teks.',
            'formData.kode_kelas.max' => 'Kode kelas tidak boleh lebih dari 12 karakter.',
            'formData.kode_kelas.unique' => 'Kode kelas sudah terdaftar, gunakan kode lain.',

            'formData.nama_kelas.required' => 'Nama kelas wajib diisi.',
            'formData.nama_kelas.string' => 'Nama kelas harus berupa teks.',
            'formData.nama_kelas.max' => 'Nama kelas tidak boleh lebih dari 15 karakter.',
        ];
    }

    #[On('openAddModal')]
    public function openAddModal()
    {
        $this->isEdit = false;
        $this->formData = [
            'kode_kelas' => '',
            'nama_kelas' => '',
            'tingkat' => '',
        ];
        Flux::modal('kelas-modal')->show();
    }

    #[On('openEditModal')]
    public function openEditModal($record)
    {
        $this->isEdit = true;
        $this->formData = $record;
        Flux::modal('kelas-modal')->show();
    }

    public function save()
    {
        $this->validate();

        if ($this->isEdit) {
            \App\Models\Kelas::find($this->formData['id'])->update([
                'kode_kelas' => $this->formData['kode_kelas'],
                'nama_kelas' => $this->formData['nama_kelas'],
                'tingkat' => strtoupper($this->formData['tingkat']),
            ]);
        } else {
            \App\Models\Kelas::create($this->formData);
        }

        Notification::make()->title('Data Kelas Tersimpan')->success()->send();
        Flux::modal('kelas-modal')->close();
        $this->dispatch('refreshTable');
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Data Kelas">
        <x-slot name="action_buttons">
            <flux:button wire:click="openAddModal" icon="plus" class="!bg-primary !text-white">Tambah Data
            </flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index actionType="data" :columns="$this->columnDefs" :model="\App\Models\Kelas::class" scope="noTingkat" />

    {{-- Add Data Modal --}}
    <flux:modal name="kelas-modal" class="md:w-96">
        <form wire:submit.prevent="save">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">
                        {{ $isEdit ? 'Ubah Data Kelas' : 'Tambah Data Kelas' }}
                    </flux:heading>
                </div>
                <flux:input wire:model.defer="formData.kode_kelas" label="Kode Kelas" placeholder="Kode Kelas" />
                <flux:field>
                    <flux:label>Tingkat</flux:label>

                    @php
                        $tingkatOptions = [['label' => 'SMP', 'value' => 'SMP'], ['label' => 'MA', 'value' => 'MA']];
                    @endphp

                    <x-select wire:model="formData.tingkat" :search="false" :options="$tingkatOptions"
                        placeholder="Pilih Tingkat" />
                    <flux:error name="formData.role" />
                </flux:field>
                <flux:input wire:model.defer="formData.nama_kelas" label="Nama Kelas" placeholder="Nama Kelas" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
