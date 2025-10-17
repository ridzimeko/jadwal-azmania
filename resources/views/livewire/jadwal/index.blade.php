<?php

use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    protected $columnDefs = [
        ['name' => 'Kelas', 'field' => 'kelas_nama'],
        ['name' => 'Hari', 'field' => 'hari'],
        ['name' => 'Jam Mulai', 'field' => 'jam_mulai'],
        ['name' => 'Jam Selesai', 'field' => 'jam_selesai'],
        ['name' => 'Mata Pelajaran', 'field' => 'mapel_nama'],
        ['name' => 'Guru Pengajar', 'field' => 'guru_nama'],
    ];

    public $tingkat;
    public $hariOptions;
    public ?array $formData = [
        'hari' => '',
        'jam_mulai' => '',
        'jam_selesai' => '',
        'kelas_id' => '',
        'mata_pelajaran_id' => '',
        'guru_id' => ''
    ];
    public ?array $filterActions;
    public bool $isEdit = false;

    public function mount($tingkat)
    {
        $this->tingkat = $tingkat;
        $this->hariOptions = collect(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])
            ->map(fn($hari) => ['label' => $hari, 'value' => $hari])
            ->toArray();
    }

    protected function rules(): array
    {
        return [
            'formData.hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'formData.jam_mulai' => 'required|date_format:H:i',
            'formData.jam_selesai' => 'required|date_format:H:i|after:formData.jam_mulai',
            'formData.kelas_id' => 'required|exists:kelas,id',
            'formData.mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'formData.guru_id' => 'required|exists:guru,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.hari.required' => 'Hari wajib diisi.',
            'formData.hari.in' => 'Hari harus salah satu dari Senin sampai Minggu.',

            'formData.jam_mulai.required' => 'Jam mulai wajib diisi.',
            'formData.jam_mulai.date_format' => 'Format jam mulai harus HH:MM (24 jam).',

            'formData.jam_selesai.required' => 'Jam selesai wajib diisi.',
            'formData.jam_selesai.date_format' => 'Format jam selesai harus HH:MM (24 jam).',
            'formData.jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',

            'formData.kelas_id.required' => 'Kelas wajib dipilih.',
            'formData.kelas_id.exists' => 'Kelas yang dipilih tidak valid.',

            'formData.mata_pelajaran_id.required' => 'Mata pelajaran wajib dipilih.',
            'formData.mata_pelajaran_id.exists' => 'Mata pelajaran yang dipilih tidak valid.',

            'formData.guru_id.required' => 'Guru wajib dipilih.',
            'formData.guru_id.exists' => 'Guru yang dipilih tidak valid.',
        ];
    }

    #[Computed()]
    public function getKelas()
    {
        return Kelas::where('tingkat', $this->tingkat)->orderBy('nama_kelas')->get();
    }

    public function openAddJadwalModal()
    {
        $this->isEdit = false;
        $this->formData = [
            'hari' => '',
            'jam_mulai' => '',
            'jam_selesai' => '',
            'kelas_id' => '',
            'mata_pelajaran_id' => '',
            'guru_id' => ''
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
            \App\Models\JadwalPelajaran::find($this->formData['id'])->update($this->formData);
        } else {
            \App\Models\JadwalPelajaran::create($this->formData);
        }

        Notification::make()
            ->title('Jadwal Berhasil Tersimpan')
            ->success()
            ->send();
        Flux::modal('jadwal-modal')->close();
        $this->dispatch('refreshJadwalTable');
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Jadwal Pelajaran {{ strtoupper($tingkat) }}"
        description="Manajemen jadwal Pelajaran untuk tingkat {{ strtoupper($tingkat) }}">
        <x-slot name="action_buttons">
            <flux:modal.trigger name="import-excel">
                <flux:button icon="file-excel" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            </flux:modal.trigger>
            <flux:button icon="plus" @click="$wire.openAddJadwalModal" class="!bg-primary !text-white">
                Tambah Data</flux:button>
            <flux:dropdown align="end">
                <flux:button icon="arrow-down-tray" icon:trailing="chevron-down">
                    Unduh Data
                </flux:button>

                <flux:menu>
                    <flux:menu.item x-on:click="window.location='{{ route('export.jadwal', ['type' => 'pdf']) }}'" icon="file-pdf">PDF</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item x-on:click="window.location='{{ route('export.jadwal', ['type' => 'excel']) }}'" icon="file-excel">Excel</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </x-slot>
    </x-card-heading>

    <div x-data="{ activeTab: 'tabel' }">
        <flux:tabs variant="segmented">
            <flux:tab
                icon="list-bullet"
                x-on:click="activeTab = 'tabel'"
                x-bind:data-selected="activeTab === 'tabel'"
                >
                Tabel
            </flux:tab>
            <flux:tab
                icon="calendar-days"
                x-on:click="activeTab = 'timeline'"
                x-bind:data-selected="activeTab === 'timeline'"
                >
                Timeline
            </flux:tab>
        </flux:tabs>

        <div class="mt-4">
            <div x-show="activeTab === 'tabel'">
                <livewire:datatable.jadwal :tingkat="$tingkat" />
            </div>
            <div x-show="activeTab === 'timeline'">
                <livewire:datatable.jadwal-matrix :tingkat="$tingkat" />
            </div>
        </div>
    </div>

    {{-- Add Data Modal --}}
    <flux:modal name="jadwal-modal" class="md:w-[720px]">
        <form wire:submit.prevent="save" class="flex flex-col gap-3 max-w-[768px]">
            <flux:heading size="lg">
                {{ $isEdit ? 'Ubah Data Jadwal' : 'Tambah Data Jadwal' }}
            </flux:heading>
            <flux:field>
                <flux:label>Nama Mata Pelajaran</flux:label>
                <x-select name="formData.mata_pelajaran_id" wire:model="formData.mata_pelajaran_id" :options="App\Models\MataPelajaran::all()
                    ->map(fn($g) => ['value' => $g->id, 'label' => $g->nama_mapel])
                    ->toArray()" placeholder="Pilih mata pelajaran..." />
                <flux:error name="formData.mata_pelajaran_id" />
            </flux:field>

            <flux:field>
                <flux:label>Kelas</flux:label>
                <x-select name="formData.kelas_id" wire:model="formData.kelas_id" :options="$this->getKelas()
                    ->map(fn($g) => ['value' => $g->id, 'label' => $g->nama_kelas])
                    ->toArray()" placeholder="Pilih kelas..." />
                <flux:error name="formData.kelas_id" />
            </flux:field>

            <flux:field>
                <flux:label>Hari</flux:label>
                <x-select name="formData.hari" wire:model="formData.hari" :search="false" :options="$this->hariOptions"
                    placeholder="Pilih hari..." />
                <flux:error name="formData.hari" />
            </flux:field>

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

            <flux:field>
                <flux:label>Guru Pengajar</flux:label>
                <x-select name="formData.guru_id" wire:model="formData.guru_id" :options="App\Models\Guru::all()
                    ->map(fn($g) => ['value' => $g->id, 'label' => $g->nama_guru])
                    ->toArray()" placeholder="Pilih guru..." />
                <flux:error name="formData.guru_id" />
            </flux:field>

            <div class="flex mt-8">
                <flux:spacer />
                <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Import Excel Modal --}}
    <livewire:excel-import-modal context="jadwal" />
</div>
