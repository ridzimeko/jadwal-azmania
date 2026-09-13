<?php

use Filament\Notifications\Notification;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new
    #[Title('Data Kelas')]
    class extends Component {
    protected $columnDefs = [['name' => 'Tingkat', 'field' => 'tingkat'], ['name' => 'Kelas', 'field' => 'nama_kelas']];

    public ?array $formData = null;
    public bool $isEdit = false;

    protected function rules(): array
    {
        return [
            'formData.nama_kelas' => ['required', 'string', 'max:20', Rule::unique('kelas', 'nama_kelas')->ignore($this->formData['id'] ?? null)],
            'formData.tingkat' => ['required', 'string', 'in:SMP,MA'],
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.nama_kelas.required' => 'Nama kelas wajib diisi.',
            'formData.nama_kelas.string' => 'Nama kelas harus berupa teks.',
            'formData.nama_kelas.max' => 'Nama kelas tidak boleh lebih dari 20 karakter.',
            'formData.nama_kelas.unique' => 'Nama kelas sudah terdaftar, gunakan nama lain.',

            'formData.tingkat.required' => 'Tingkat wajib dipilih.',
            'formData.tingkat.in' => 'Tingkat harus salah satu dari: SMP, MA.',
        ];
    }

    #[On('openAddModal')]
    public function openAddModal()
    {
        $this->isEdit = false;
        $this->formData = [
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
                'nama_kelas' => $this->formData['nama_kelas'],
                'tingkat' => strtoupper($this->formData['tingkat']),
            ]);
        } else {
            \App\Models\Kelas::create([
                'nama_kelas' => $this->formData['nama_kelas'],
                'tingkat' => strtoupper($this->formData['tingkat']),
            ]);
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
            <flux:modal.trigger name="import-excel">
                <flux:button icon="file-excel" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            </flux:modal.trigger>
            <flux:button wire:click="openAddModal" icon="plus" class="!bg-primary !text-white">Tambah Data
            </flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index actionType="data" :columns="$this->columnDefs" :model="\App\Models\Kelas::class"
        scope="noTingkat" />

    {{-- Add Data Modal --}}
    <flux:modal name="kelas-modal" class="w-[85%] md:w-[480px]">
        <form wire:submit.prevent="save">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">
                        {{ $isEdit ? 'Ubah Data Kelas' : 'Tambah Data Kelas' }}
                    </flux:heading>
                </div>
                <flux:field>
                    <flux:label>Tingkat</flux:label>

                    @php
                        $tingkatOptions = [['label' => 'SMP', 'value' => 'SMP'], ['label' => 'MA', 'value' => 'MA']];
                    @endphp

                    <x-select wire:model="formData.tingkat" :search="false" :options="$tingkatOptions"
                        placeholder="Pilih Tingkat" />
                    <flux:error name="formData.tingkat" />
                </flux:field>
                <flux:input wire:model.defer="formData.nama_kelas" label="Nama Kelas" placeholder="Nama Kelas" />
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