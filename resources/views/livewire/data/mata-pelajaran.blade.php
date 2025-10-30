<?php

use Filament\Forms\Components\ColorPicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
    #[Title('Mata Pelajaran')]
    class extends Component implements HasSchemas {
        use InteractsWithSchemas;

        protected $columnDefs = [
            ['name' => 'Kode Mapel', 'field' => 'kode_mapel'],
            ['name' => 'Mata Pelajaran', 'field' => 'nama_mapel'],
            ['name' => 'Warna', 'field' => 'warna'],
        ];

        public array $formData = [
            'kode_mapel' => '',
            'nama_mapel' => '',
            'warna' => '',
            'kategori' => '',
        ];
        public bool $isEdit = false;

        protected function rules(): array
        {
            return [
                'formData.kode_mapel' => [
                    'required',
                    'string',
                    'max:12',
                    Rule::unique('mata_pelajaran', 'kode_mapel')->ignore($this->formData['id'] ?? null),
                ],
                'formData.nama_mapel' => [
                    'required',
                    'string',
                    'max:40',
                ],
                'formData.warna' => ['hex_color']
            ];
        }

        protected function messages(): array
        {
            return [
                'formData.kode_mapel.required' => 'Kode mata pelajaran wajib diisi.',
                'formData.kode_mapel.string' => 'Kode mata pelajaran harus berupa teks.',
                'formData.kode_mapel.max' => 'Kode mata pelajaran tidak boleh lebih dari 12 karakter.',
                'formData.kode_mapel.unique' => 'Kode mata pelajaran sudah terdaftar, gunakan kode lain.',

                'formData.nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
                'formData.nama_mapel.string' => 'Nama mata pelajaran harus berupa teks.',
                'formData.nama_mapel.max' => 'Nama mata pelajaran tidak boleh lebih dari 40 karakter.',

                'formData.warna.hex_color' => 'Warna harus dalam format heksadesimal.'
            ];
        }

        public function form(Schema $schema): Schema
        {
            return $schema
                ->components([
                    ColorPicker::make('formData.warna')->label('Warna')->placeholder('Pilih warna untuk jadwal')->regex('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})\b$/')
                ]);
        }

        #[On('openAddModal')]
        public function openAddModal()
        {
            $this->isEdit = false;
            $this->formData = [
                'kode_mapel' => '',
                'nama_mapel' => '',
                'warna' => '',
                'kategori' => '',
            ];
            Flux::modal('mapel-modal')->show();
        }

        #[On('openEditModal')]
        public function openEditModal($record)
        {
            $this->isEdit = true;
            $this->formData = $record;
            Flux::modal('mapel-modal')->show();
        }

        public function save()
        {
            $this->validate();

            if ($this->isEdit) {
                \App\Models\MataPelajaran::find($this->formData['id'])->update($this->formData);
            } else {
                \App\Models\MataPelajaran::create($this->formData);
            }

            Notification::make()
                ->title('Mata Pelajaran Tersimpan')
                ->success()
                ->send();
            Flux::modal('mapel-modal')->close();
            $this->dispatch('refreshTable');
        }
    };
?>

<div class="dash-card">
    <x-card-heading title="Data Mata Pelajaran">
        <x-slot name="action_buttons">
            <flux:modal.trigger name="import-excel">
                <flux:button icon="file-excel" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            </flux:modal.trigger>
            <flux:button wire:click="openAddModal" icon="plus" class="!bg-primary !text-white">Tambah Data</flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index actionType="data" :columns="$this->columnDefs" :model="\App\Models\MataPelajaran::class" />

    {{-- Add Data Modal --}}
    <flux:modal name="mapel-modal" class="md:w-96">
        <form wire:submit.prevent="save">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">
                        {{ $isEdit ? 'Ubah Data Mata Pelajaran' : 'Tambah Data Mata Pelajaran' }}
                    </flux:heading>
                </div>
                <flux:input wire:model.defer="formData.kode_mapel" label="Kode Mapel" placeholder="Kode Mapel" />
                <flux:input wire:model.defer="formData.nama_mapel" label="Nama Mapel" placeholder="Nama Mapel" />

                <flux:field>
                    <flux:label>Kategori</flux:label>
                    <x-select
                        name="formData.kategori"
                        wire:model="formData.kategori"
                        :search="false"
                        :options="[
                        ['label' => 'KBM', 'value' => 'KBM'],
                        ['label' => 'Non KBM', 'value' => 'Non KBM'],
                    ]"
                        placeholder="Pilih kategori mata pelajaran"
                        clearable />
                    <flux:error name="formData.kategori" />
                </flux:field>

                {{-- filament form --}}
                {{ $this->form }}

                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    {{-- Import Excel Modal --}}
    <livewire:excel-import-modal context="mapel" />
</div>