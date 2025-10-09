<?php

use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    protected $columnDefs = [['name' => 'NIP', 'field' => 'nip'], ['name' => 'Nama Guru', 'field' => 'nama_guru']];

    public ?array $formData = null;
    public bool $isEdit = false;

    protected function rules(): array
    {
        return [
            'formData.nip' => ['required', 'digits_between:8,20',  Rule::unique('guru', 'nip')->ignore($this->formData['id'] ?? null)],
            'formData.nama_guru' => ['required', 'string', 'max:40'],
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.nip.required' => 'NIP wajib diisi.',
            'formData.nip.digits_between' => 'NIP harus terdiri dari 8 hingga 20 angka.',
            'formData.nip.unique' => 'NIP ini sudah digunakan oleh guru lain.',
            'formData.nama_guru.required' => 'Nama guru wajib diisi.',
            'formData.nama_guru.max' => 'Nama guru tidak boleh lebih dari 40 karakter.',
        ];
    }

    #[On('openAddModal')]
    public function openAddModal()
    {
        $this->isEdit = false;
        $this->formData = [
            'nama_guru' => '',
            'nip' => '',
        ];
        Flux::modal('guru-modal')->show();
    }

    #[On('openEditModal')]
    public function openEditModal($record)
    {
        $this->isEdit = true;
        $this->formData = $record;
        Flux::modal('guru-modal')->show();
    }

    public function save()
    {
        $this->validate();

        if ($this->isEdit) {
            \App\Models\Guru::find($this->formData['id'])->update($this->formData);
        } else {
            \App\Models\Guru::create($this->formData);
        }

        Flux::modal('guru-modal')->close();
        $this->dispatch('refreshTable');
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Data Guru">
        <x-slot name="action_buttons">
            <flux:modal.trigger name="import-excel">
                <flux:button icon="file-excel" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            </flux:modal.trigger>
            <flux:button @click="$wire.openAddModal" icon="plus" class="!bg-primary !text-white">Tambah Data</flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index :columns="$this->columnDefs" :model="\App\Models\Guru::class" />

    {{-- Import Excel Modal --}}
    <livewire:excel-import-modal context="guru" />

    {{-- Add Data Modal --}}
    <flux:modal name="guru-modal" class="md:w-96">
        <form wire:submit.prevent="save">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">
                        {{ $isEdit ? 'Ubah Data Guru' : 'Tambah Data Guru' }}
                    </flux:heading>
                </div>
                <flux:input wire:model.defer="formData.nip" label="NIP" placeholder="NIP" />
                <flux:input wire:model.defer="formData.nama_guru" label="Nama Guru" placeholder="Nama Guru" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
