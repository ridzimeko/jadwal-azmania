<?php

use App\Helpers\JadwalHelper;
use App\Models\JadwalPelajaran;
use App\Rules\JatahJpRule;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Jadwal Pelajaran')] class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected $columnDefs = [['name' => 'Kelas', 'field' => 'kelas_nama'], ['name' => 'Hari', 'field' => 'hari'], ['name' => 'Jam Mulai', 'field' => 'jam_mulai'], ['name' => 'Jam Selesai', 'field' => 'jam_selesai'], ['name' => 'Mata Pelajaran', 'field' => 'mapel_nama'], ['name' => 'Guru Pengajar', 'field' => 'guru_nama']];

    public $periode_id;
    public $tahunAjaran;
    public $hariOptions;
    public $kelasOptions;
    public $guruOptions;
    public $jamPelajaranOptions;
    public $jadwalBentrokList = [];
    public ?array $formData = [
        'hari' => '',
        'jam_mulai' => '',
        'jam_selesai' => '',
        'kelas_id' => '',
        'mata_pelajaran_id' => '',
        'guru_id' => '',
        'jam_pelajaran_id' => '',
    ];
    public ?array $filterData = [
        'hari' => '',
        'tingkat' => '',
    ];
    public bool $isEdit = false;

    public function mount()
    {
        $this->tahunAjaran = JadwalHelper::getTahunAjaran($this->periode_id);

        if (!$this->tahunAjaran) {
            abort(404, 'Jadwal dengan periode ini tidak ditemukan.');
        }

        $this->hariOptions = JadwalHelper::getHariOptions();
        $this->kelasOptions = JadwalHelper::getKelasOptions($this->filterData['tingkat']);
        $this->guruOptions = JadwalHelper::getGuruOptions();
        $this->jamPelajaranOptions = JadwalHelper::getJamPelajaranOptions();
        // $this->filterData['hari'] = 'Senin';
        $this->filterData['tingkat'] = 'smp';
    }

    protected function rules(): array
    {
        return [
            'formData.hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'formData.kelas_id' => 'required|exists:kelas,id',
            'formData.mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id', new JatahJpRule($this->periode_id, $this->formData['id'] ?? null)],
            'formData.jam_pelajaran_id' => 'required|exists:jam_pelajaran,id',
            'formData.guru_id' => 'nullable|exists:guru,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.hari.required' => 'Hari wajib diisi.',
            'formData.hari.in' => 'Hari harus salah satu dari Senin sampai Minggu.',

            'formData.kelas_id.required' => 'Kelas wajib dipilih.',
            'formData.kelas_id.exists' => 'Kelas yang dipilih tidak valid.',

            'formData.mata_pelajaran_id.required' => 'Mata pelajaran wajib dipilih.',
            'formData.mata_pelajaran_id.exists' => 'Mata pelajaran yang dipilih tidak valid.',

            'formData.jam_pelajaran_id.required' => 'Jam pelajaran wajib dipilih.',
            'formData.jam_pelajaran_id.exists' => 'Jam pelajaran yang dipilih tidak valid.',

            'formData.guru_id.exists' => 'Guru yang dipilih tidak valid.',
        ];
    }

    #[Computed]
    public function mataPelajaranOptions()
    {
        return JadwalHelper::getMapelWithJpOptions($this->periode_id);
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
            'guru_id' => '',
            'jam_pelajaran_id' => '',
        ];
        Flux::modal('jadwal-modal')->show();
    }

    #[On('openEditJadwal')]
    public function openEditJadwal($record)
    {
        if ($record['id'] ?? null) {
            $this->isEdit = true;
        } else {
            $this->isEdit = false;
        }
        $this->formData = $record;
        Flux::modal('jadwal-modal')->show();
    }

    public function save()
    {
        $this->validate();

        $jadwal = JadwalHelper::isAvailable($this->formData, $this->formData['id'] ?? null);
        if (!$jadwal['available']) {
            $this->jadwalBentrokList = $jadwal['bentrok'];
            return;
        }

        $result = [
            ...JadwalHelper::empty_to_null($this->formData),
            'periode_id' => $this->periode_id,
        ];

        if ($this->isEdit) {
            JadwalPelajaran::find($this->formData['id'])->update($result);
        } else {
            JadwalPelajaran::create($result);
        }

        $this->jadwalBentrokList = [];
        Notification::make()->title('Jadwal Berhasil Tersimpan')->success()->send();
        Flux::modal('jadwal-modal')->close();
        $this->dispatch('refreshJadwalTable');
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Hapus')
            ->color('danger')
            ->modalHeading('Hapus Jadwal')
            ->modalDescription('Apakah anda yakin ingin menghapus data ini?')
            ->requiresConfirmation()
            ->modalHeading('Hapus Jadwal')
            ->modalDescription('Apakah anda yakin ingin menghapus data ini?')
            ->action(function (array $arguments) {
                $post = JadwalPelajaran::find($arguments['jadwal']);

                $post?->delete();

                Notification::make()->title('Jadwal berhasil dihapus')->success()->send();
                Flux::modal('jadwal-modal')->close();
                $this->dispatch('refreshJadwalTable');
            });
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Jadwal Pelajaran" description="Periode Tahun Ajaran {{ $this->tahunAjaran }}">
        <x-slot name="action_buttons">
            <flux:modal.trigger name="import-excel">
                <flux:button icon="file-excel" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            </flux:modal.trigger>
            <flux:button icon="plus" wire:click="openAddJadwalModal" class="!bg-primary !text-white">
                Tambah Data
            </flux:button>
            <flux:modal.trigger name="export-jadwal">
                <flux:button icon="arrow-down-tray">
                    Unduh Data
                </flux:button>
            </flux:modal.trigger>
        </x-slot>
    </x-card-heading>

    <div x-data="{ activeTab: 'tabel' }">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <flux:tabs variant="segmented">
                <flux:tab icon="list-bullet" x-on:click="activeTab = 'tabel'"
                    x-bind:data-selected="activeTab === 'tabel'">
                    Tabel
                </flux:tab>
                <flux:tab icon="calendar-days" x-on:click="activeTab = 'timeline'"
                    x-bind:data-selected="activeTab === 'timeline'">
                    Timeline
                </flux:tab>
            </flux:tabs>

            <div x-cloak x-show="activeTab === 'timeline'" class="flex items-center flex-wrap gap-4">
                <x-select wire:model.live="filterData.hari" :search="false" :options="JadwalHelper::getHariOptions(true)" placeholder="Pilih hari"
                    class="!w-[140px]" />
                <x-select wire:model.live="filterData.tingkat" :search="false" :options="[['label' => 'SMP', 'value' => 'smp'], ['label' => 'MA', 'value' => 'ma']]"
                    placeholder="Pilih tingkat" class="!w-[120px]" />
            </div>
        </div>

        <div class="mt-4">
            <div x-show="activeTab === 'tabel'">
                <livewire:datatable.jadwal :periode_id="$this->periode_id" />
            </div>
            <div x-cloak x-show="activeTab === 'timeline'">
                <livewire:datatable.jadwal-matrix lazy :periode_id="$this->periode_id" :hari="$this->filterData['hari']" :tingkat="$this->filterData['tingkat']" wire:key="matrix-{{ md5(json_encode($filterData)) }}" />
            </div>
        </div>
    </div>

    <x-filament-actions::modals />

    {{-- Add Data Modal --}}
    <flux:modal name="jadwal-modal" class="w-[90%] md:w-[480px] z-[30]" variant="flyout">
        <form wire:submit.prevent="save" class="flex flex-col gap-3 max-w-[768px]">
            <flux:heading size="lg">
                {{ $isEdit ? 'Ubah Data Jadwal' : 'Tambah Data Jadwal' }}
            </flux:heading>

            @if (count($this->jadwalBentrokList) >= 1)
                <flux:callout variant="danger" icon="x-circle" heading="Jadwal terjadi bentrok dengan:">
                    <flux:callout.text>
                        <ul>
                            @foreach ($this->jadwalBentrokList as $jadwal)
                                <li>
                                    <div>{{ $jadwal['kelas'] }} {{ $jadwal['jam_mulai'] }} -
                                        {{ $jadwal['jam_selesai'] }} ({{ $jadwal['guru'] }} / {{ $jadwal['mapel'] }})
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </flux:callout.text>
                </flux:callout>
            @endif

            <flux:field>
                <flux:label>Nama Mata Pelajaran</flux:label>
                <x-select name="formData.mata_pelajaran_id" wire:model="formData.mata_pelajaran_id" :options="$this->mataPelajaranOptions"
                    placeholder="Pilih mata pelajaran..." />
                <flux:error name="formData.mata_pelajaran_id" />
            </flux:field>

            <flux:field>
                <flux:label>Kelas</flux:label>
                <x-select name="formData.kelas_id" wire:model="formData.kelas_id" :options="$kelasOptions"
                    placeholder="Pilih kelas..." />
                <flux:error name="formData.kelas_id" />
            </flux:field>

            <flux:field>
                <flux:label>Hari</flux:label>
                <x-select name="formData.hari" wire:model="formData.hari" :search="false" :options="$this->hariOptions"
                    placeholder="Pilih hari..." />
                <flux:error name="formData.hari" />
            </flux:field>

             <flux:field>
                <flux:label>Jam ke</flux:label>
                <x-select name="formData.jam_pelajaran_id" wire:model="formData.jam_pelajaran_id" :options="$this->jamPelajaranOptions"
                    placeholder="Pilih jam pelajaran..." />
                <flux:error name="formData.jam_pelajaran_id" />
            </flux:field>

            <flux:field>
                <flux:label>Guru Pengajar</flux:label>
                <x-select name="formData.guru_id" wire:model="formData.guru_id" :options="$this->guruOptions"
                    placeholder="Pilih guru..." clearable />
                <flux:error name="formData.guru_id" />
            </flux:field>

            <div class="flex mt-8">
                @if ($this->isEdit)
                    <flux:button variant="primary" color="red" icon="trash"
                        x-on:click="() => {
                        $flux.modals().close()
                        $wire.mountAction('delete', { jadwal: '{{ $this->formData['id'] ?? null }}' })
                    }">
                        Hapus</flux:button>
                @endif
                <flux:spacer />
                <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>

    <livewire:export-jadwal-modal :periodeId="$this->periode_id" />

    {{-- Import Excel Modal --}}
    <div>
        <livewire:excel-import-modal context="jadwal" :periodeId="$this->periode_id" />
    </div>
</div>
