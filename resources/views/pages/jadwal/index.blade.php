<?php

use App\Helpers\JadwalHelper;
use App\Models\JadwalPelajaran;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

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
    public $availableSlotsList = [];
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
        'guru_id' => '',
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
            'formData.mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'formData.guru_id' => 'nullable|exists:guru,id',
            'formData.jam_pelajaran_ids' => 'required|array|min:1',
            'formData.jam_pelajaran_ids.*' => 'exists:jam_pelajaran,id',
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
            'formData.jam_pelajaran_ids.required' => 'Pilih minimal satu jam pelajaran.',
            'formData.jam_pelajaran_ids.min' => 'Pilih minimal satu jam pelajaran.',

            'formData.guru_id.exists' => 'Guru yang dipilih tidak valid.',
        ];
    }

    public function openAddJadwalModal($record = [])
    {
        $this->isEdit = false;
        $this->jadwalBentrokList = [];
        $this->availableSlotsList = [];
        $firstKelas = !empty($this->kelasOptions) ? $this->kelasOptions[0]['value'] : '';
        $this->formData = [
            'hari' => $record['hari'] ?? $this->filterData['hari'] ?? 'Senin',
            'jam_mulai' => '',
            'jam_selesai' => '',
            'kelas_id' => $record['kelas_id'] ?? $firstKelas,
            'mata_pelajaran_id' => '',
            'guru_id' => '',
            'jam_pelajaran_id' => $record['jam_pelajaran_id'] ?? '',
            'jam_pelajaran_ids' => isset($record['jam_pelajaran_id']) ? [(string) $record['jam_pelajaran_id']] : [],
        ];
        Flux::modal('jadwal-modal')->show();
    }

    #[On('openEditJadwal')]
    public function openEditJadwal($record)
    {
        $this->jadwalBentrokList = [];
        $this->availableSlotsList = [];

        if (!empty($record['id'])) {
            $this->isEdit = true;
            if (!empty($record['jam_pelajaran_ids']) && is_array($record['jam_pelajaran_ids'])) {
                $record['jam_pelajaran_ids'] = array_map('strval', $record['jam_pelajaran_ids']);
            } else {
                $item = JadwalPelajaran::find($record['id']);
                if ($item) {
                    $matchingJamIds = JadwalPelajaran::where('periode_id', $item->periode_id)
                        ->where('hari', $item->hari)
                        ->where('kelas_id', $item->kelas_id)
                        ->where('mata_pelajaran_id', $item->mata_pelajaran_id)
                        ->where('guru_id', $item->guru_id)
                        ->pluck('jam_pelajaran_id')
                        ->map(fn($id) => (string) $id)
                        ->toArray();
                    $record['jam_pelajaran_ids'] = !empty($matchingJamIds) ? $matchingJamIds : [(string) $item->jam_pelajaran_id];
                } else {
                    $record['jam_pelajaran_ids'] = isset($record['jam_pelajaran_id']) ? [(string) $record['jam_pelajaran_id']] : [];
                }
            }
        } else {
            $this->isEdit = false;
            if (!empty($record['jam_pelajaran_ids']) && is_array($record['jam_pelajaran_ids'])) {
                $record['jam_pelajaran_ids'] = array_map('strval', $record['jam_pelajaran_ids']);
            } elseif (isset($record['jam_pelajaran_id']) && $record['jam_pelajaran_id'] !== '') {
                $record['jam_pelajaran_ids'] = [(string) $record['jam_pelajaran_id']];
            } else {
                $record['jam_pelajaran_ids'] = [];
            }
        }
        $this->formData = $record;
        Flux::modal('jadwal-modal')->show();
    }

    public function updatedFormData($value, $key)
    {
        if (in_array($key, ['hari', 'kelas_id', 'guru_id', 'jam_pelajaran_ids'])) {
            $this->jadwalBentrokList = [];
            $this->availableSlotsList = [];
        }
    }

    public function prepareBentrokListWithSlots(array $rawList)
    {
        $prepared = [];
        foreach ($rawList as $index => $item) {
            $data = $item['data'] ?? [
                'hari' => $item['hari'] ?? $this->formData['hari'] ?? 'Senin',
                'kelas_id' => $item['kelas_id'] ?? $this->formData['kelas_id'] ?? null,
                'mata_pelajaran_id' => $item['mata_pelajaran_id'] ?? $this->formData['mata_pelajaran_id'] ?? null,
                'guru_id' => $item['guru_id'] ?? $this->formData['guru_id'] ?? null,
                'periode_id' => $this->periode_id,
            ];

            $selectedHari = $data['hari'] ?? 'Senin';
            $availableSlots = [];
            if (!empty($data['kelas_id']) && !empty($selectedHari)) {
                $data['hari'] = $selectedHari;
                $availableSlots = JadwalHelper::findAvailableSlots($data)->toArray();
            }

            $prepared[] = array_merge($item, [
                'index' => $index,
                'data' => $data,
                'selected_hari' => $selectedHari,
                'available_slots' => $availableSlots,
                'selected_slot_id' => null,
            ]);
        }
        return $prepared;
    }

    #[On('openBentrokSummaryModal')]
    public function openBentrokSummaryModal($bentrokList)
    {
        $this->jadwalBentrokList = $this->prepareBentrokListWithSlots($bentrokList);
        $this->availableSlotsList = [];
        Flux::modal('jadwal-bentrok-modal')->show();
    }

    public function changeHariForBentrokItem($itemIndex, $newHari)
    {
        if (isset($this->jadwalBentrokList[$itemIndex])) {
            $this->jadwalBentrokList[$itemIndex]['selected_hari'] = $newHari;
            $this->jadwalBentrokList[$itemIndex]['data']['hari'] = $newHari;
            $this->jadwalBentrokList[$itemIndex]['selected_slot_id'] = null;

            $data = $this->jadwalBentrokList[$itemIndex]['data'];
            if (!empty($data['kelas_id']) && !empty($newHari)) {
                $this->jadwalBentrokList[$itemIndex]['available_slots'] = JadwalHelper::findAvailableSlots($data)->toArray();
            } else {
                $this->jadwalBentrokList[$itemIndex]['available_slots'] = [];
            }
        }
    }

    public function getTakenSlotsMap()
    {
        $taken = [];
        foreach ($this->jadwalBentrokList as $item) {
            $hari = $item['selected_hari'] ?? $item['data']['hari'] ?? null;
            $slotId = $item['selected_slot_id'] ?? null;
            if ($hari && $slotId) {
                $kelasId = $item['data']['kelas_id'] ?? null;
                $guruId = $item['data']['guru_id'] ?? null;
                $taken["{$hari}_{$slotId}"] = true;
                if ($kelasId) {
                    $taken["{$hari}_{$slotId}_k_{$kelasId}"] = true;
                }
                if ($guruId) {
                    $taken["{$hari}_{$slotId}_g_{$guruId}"] = true;
                }
            }
        }
        return $taken;
    }

    public function selectSlotForBentrokItem($itemIndex, $slotId)
    {
        if (isset($this->jadwalBentrokList[$itemIndex])) {
            if (($this->jadwalBentrokList[$itemIndex]['selected_slot_id'] ?? null) == $slotId) {
                $this->jadwalBentrokList[$itemIndex]['selected_slot_id'] = null;
            } else {
                $this->jadwalBentrokList[$itemIndex]['selected_slot_id'] = (string) $slotId;
            }
        }
    }

    public function saveBentrokAllocations()
    {
        if (empty($this->jadwalBentrokList)) {
            return;
        }

        $savedCount = 0;
        $taken = [];

        foreach ($this->jadwalBentrokList as $item) {
            $data = $item['data'] ?? [];
            $hari = $item['selected_hari'] ?? $data['hari'] ?? 'Senin';
            $data['hari'] = $hari;

            $slotId = $item['selected_slot_id'] ?? null;

            if (!$slotId && !empty($item['available_slots'])) {
                // Pick first available slot not in $taken
                foreach ($item['available_slots'] as $avail) {
                    $sId = (string) $avail['id'];
                    $kId = $data['kelas_id'] ?? null;
                    $gId = $data['guru_id'] ?? null;

                    $isTaken = isset($taken["{$hari}_{$sId}"]) ||
                        ($kId && isset($taken["{$hari}_{$sId}_k_{$kId}"])) ||
                        ($gId && isset($taken["{$hari}_{$sId}_g_{$gId}"]));

                    if (!$isTaken) {
                        $slotId = $sId;
                        break;
                    }
                }
            }

            if (!$slotId) {
                continue;
            }

            if (empty($data['kelas_id']) || empty($data['mata_pelajaran_id']) || empty($data['hari'])) {
                continue;
            }

            $data['jam_pelajaran_id'] = $slotId;
            unset($data['jam_pelajaran_ids']);

            JadwalPelajaran::create(JadwalHelper::empty_to_null($data));

            $kId = $data['kelas_id'] ?? null;
            $gId = $data['guru_id'] ?? null;
            $taken["{$hari}_{$slotId}"] = true;
            if ($kId) $taken["{$hari}_{$slotId}_k_{$kId}"] = true;
            if ($gId) $taken["{$hari}_{$slotId}_g_{$gId}"] = true;

            $savedCount++;
        }

        $this->jadwalBentrokList = [];
        $this->availableSlotsList = [];

        if ($savedCount > 0) {
            Notification::make()
                ->title("{$savedCount} Jadwal Berhasil Dialokasikan!")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("Tidak ada slot jam yang dipilih.")
                ->warning()
                ->send();
            return;
        }

        Flux::modal('jadwal-bentrok-modal')->close();
        Flux::modal('jadwal-modal')->close();
        $this->dispatch('refreshJadwalTable');
        $this->dispatch('reload-mapel-options');
    }

    public function autoResolveBentrok()
    {
        if (empty($this->jadwalBentrokList)) {
            return;
        }

        $resolvedCount = 0;
        $allDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $taken = [];

        foreach ($this->jadwalBentrokList as $item) {
            $data = $item['data'] ?? null;
            if (!$data) {
                $data = [
                    'hari' => $item['selected_hari'] ?? $item['hari'] ?? $this->formData['hari'] ?? 'Senin',
                    'kelas_id' => $item['kelas_id'] ?? $this->formData['kelas_id'] ?? null,
                    'mata_pelajaran_id' => $item['mata_pelajaran_id'] ?? $this->formData['mata_pelajaran_id'] ?? null,
                    'guru_id' => $item['guru_id'] ?? $this->formData['guru_id'] ?? null,
                    'periode_id' => $this->periode_id,
                ];
            }

            if (empty($data['kelas_id']) || empty($data['mata_pelajaran_id'])) {
                continue;
            }

            $currentHari = $item['selected_hari'] ?? $data['hari'] ?? 'Senin';
            $searchDays = array_unique(array_merge([$currentHari], $allDays));

            foreach ($searchDays as $day) {
                $data['hari'] = $day;
                $available = JadwalHelper::findAvailableSlots($data);

                $validSlot = $available->first(function ($s) use ($taken, $day, $data) {
                    $sId = (string) $s['id'];
                    $kId = $data['kelas_id'] ?? null;
                    $gId = $data['guru_id'] ?? null;

                    return !isset($taken["{$day}_{$sId}"]) &&
                        (!$kId || !isset($taken["{$day}_{$sId}_k_{$kId}"])) &&
                        (!$gId || !isset($taken["{$day}_{$sId}_g_{$gId}"]));
                });

                if ($validSlot) {
                    $sId = (string) $validSlot['id'];
                    $kId = $data['kelas_id'] ?? null;
                    $gId = $data['guru_id'] ?? null;

                    $data['jam_pelajaran_id'] = $sId;
                    unset($data['jam_pelajaran_ids']);
                    JadwalPelajaran::create(JadwalHelper::empty_to_null($data));

                    $taken["{$day}_{$sId}"] = true;
                    if ($kId) $taken["{$day}_{$sId}_k_{$kId}"] = true;
                    if ($gId) $taken["{$day}_{$sId}_g_{$gId}"] = true;

                    $resolvedCount++;
                    break;
                }
            }
        }

        $this->jadwalBentrokList = [];
        $this->availableSlotsList = [];

        if ($resolvedCount > 0) {
            Notification::make()
                ->title("{$resolvedCount} Jadwal Berhasil Dialokasikan Otomatis!")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("Tidak ada slot jam kosong yang tersedia di hari manapun.")
                ->danger()
                ->send();
        }

        Flux::modal('jadwal-bentrok-modal')->close();
        Flux::modal('jadwal-modal')->close();
        $this->dispatch('refreshJadwalTable');
        $this->dispatch('reload-mapel-options');
    }

    public function save()
    {
        $this->validate();

        $jamIds = $this->formData['jam_pelajaran_ids'] ?? [];
        if (empty($jamIds) && !empty($this->formData['jam_pelajaran_id'])) {
            $jamIds = [(string) $this->formData['jam_pelajaran_id']];
        }

        if ($this->isEdit) {
            $editId = $this->formData['id'] ?? null;
            $originalRecord = JadwalPelajaran::find($editId);
            $oldBlockIds = [];
            if ($originalRecord) {
                $oldBlockIds = JadwalPelajaran::where('periode_id', $originalRecord->periode_id)
                    ->where('hari', $originalRecord->hari)
                    ->where('kelas_id', $originalRecord->kelas_id)
                    ->where('mata_pelajaran_id', $originalRecord->mata_pelajaran_id)
                    ->where('guru_id', $originalRecord->guru_id)
                    ->pluck('id')
                    ->toArray();
            }
            if (empty($oldBlockIds) && $editId) {
                $oldBlockIds = [$editId];
            }

            // Check availability for all selected jam slots
            $allBentrok = collect();
            foreach ($jamIds as $jamId) {
                $singleData = array_merge($this->formData, ['jam_pelajaran_id' => $jamId, 'periode_id' => $this->periode_id]);
                $chk = JadwalHelper::isAvailable($singleData, $oldBlockIds);
                if (!$chk['available']) {
                    $allBentrok = $allBentrok->concat($chk['bentrok']);
                }
            }

            if ($allBentrok->isNotEmpty()) {
                $this->jadwalBentrokList = $allBentrok->unique('id')->values()->toArray();
                $checkData = array_merge($this->formData, ['periode_id' => $this->periode_id]);
                $this->availableSlotsList = JadwalHelper::findAvailableSlots($checkData, $oldBlockIds)->toArray();
                return;
            }

            // Replace old block records with the newly selected jam slots
            if (!empty($oldBlockIds)) {
                JadwalPelajaran::whereIn('id', $oldBlockIds)->delete();
            }

            foreach ($jamIds as $jamId) {
                $result = [
                    'hari' => $this->formData['hari'],
                    'kelas_id' => $this->formData['kelas_id'],
                    'mata_pelajaran_id' => $this->formData['mata_pelajaran_id'],
                    'guru_id' => JadwalHelper::empty_to_null($this->formData)['guru_id'] ?? null,
                    'jam_pelajaran_id' => $jamId,
                    'periode_id' => $this->periode_id,
                ];
                JadwalPelajaran::create($result);
            }
        } else {
            // Check availability for all selected jam slots first
            $allBentrok = collect();
            foreach ($jamIds as $jamId) {
                $singleData = array_merge($this->formData, ['jam_pelajaran_id' => $jamId, 'periode_id' => $this->periode_id]);
                $chk = JadwalHelper::isAvailable($singleData);
                if (!$chk['available']) {
                    $allBentrok = $allBentrok->concat($chk['bentrok']);
                }
            }

            if ($allBentrok->isNotEmpty()) {
                $this->jadwalBentrokList = $allBentrok->unique('id')->values()->toArray();
                $checkData = array_merge($this->formData, ['periode_id' => $this->periode_id]);
                $this->availableSlotsList = JadwalHelper::findAvailableSlots($checkData)->toArray();
                return;
            }

            // Create all schedule items
            foreach ($jamIds as $jamId) {
                $result = [
                    'hari' => $this->formData['hari'],
                    'kelas_id' => $this->formData['kelas_id'],
                    'mata_pelajaran_id' => $this->formData['mata_pelajaran_id'],
                    'guru_id' => JadwalHelper::empty_to_null($this->formData)['guru_id'] ?? null,
                    'jam_pelajaran_id' => $jamId,
                    'periode_id' => $this->periode_id,
                ];
                JadwalPelajaran::create($result);
            }
        }

        $this->jadwalBentrokList = [];
        $this->availableSlotsList = [];
        Notification::make()->title('Jadwal Berhasil Tersimpan')->success()->send();
        Flux::modal('jadwal-modal')->close();
        $this->dispatch('refreshJadwalTable');
        $this->dispatch('reload-mapel-options');
    }

    public function selectAlternativeSlot($slotId)
    {
        $jamIds = $this->formData['jam_pelajaran_ids'] ?? [];
        if (!in_array((string) $slotId, $jamIds)) {
            $jamIds[] = (string) $slotId;
        }
        $this->formData['jam_pelajaran_ids'] = array_values(array_unique($jamIds));
        $this->jadwalBentrokList = [];
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Hapus')
            ->color('danger')
            ->modalHeading('Hapus Jadwal')
            ->modalDescription('Apakah anda yakin ingin menghapus data ini?')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $post = JadwalPelajaran::find($arguments['jadwal'] ?? null);

                if ($post) {
                    $jamIds = $this->formData['jam_pelajaran_ids'] ?? [];
                    if (!empty($jamIds) && is_array($jamIds)) {
                        JadwalPelajaran::where('periode_id', $post->periode_id)
                            ->where('hari', $post->hari)
                            ->where('kelas_id', $post->kelas_id)
                            ->where('mata_pelajaran_id', $post->mata_pelajaran_id)
                            ->where('guru_id', $post->guru_id)
                            ->whereIn('jam_pelajaran_id', $jamIds)
                            ->delete();
                    } else {
                        JadwalPelajaran::where('periode_id', $post->periode_id)
                            ->where('hari', $post->hari)
                            ->where('kelas_id', $post->kelas_id)
                            ->where('mata_pelajaran_id', $post->mata_pelajaran_id)
                            ->where('guru_id', $post->guru_id)
                            ->delete();
                    }
                }

                Notification::make()->title('Jadwal berhasil dihapus')->success()->send();
                Flux::modal('jadwal-modal')->close();
                $this->dispatch('refreshJadwalTable');
                $this->dispatch('reload-mapel-options');
            });
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Jadwal Pelajaran" description="Periode Tahun Ajaran {{ $this->tahunAjaran }}">
        <x-slot name="action_buttons">
            @if(auth()->user()->role !== 'guru')
                {{-- Fitur Import Excel disembunyikan sementara --}}
                {{-- <flux:modal.trigger name="import-excel">
                    <flux:button icon="file-excel" class="!bg-az-green !text-white">Import dari Excel</flux:button>
                </flux:modal.trigger> --}}
                <flux:button icon="plus" wire:click="openAddJadwalModal" class="!bg-primary !text-white">
                    Tambah Data
                </flux:button>
            @endif
            <flux:modal.trigger name="export-jadwal">
                <flux:button icon="arrow-down-tray">
                    Unduh Data
                </flux:button>
            </flux:modal.trigger>
        </x-slot>
    </x-card-heading>

    <div x-data="{ activeTab: 'timeline' }">
        <div class="flex items-center justify-end gap-4 flex-wrap">
            {{-- Tabs disembunyikan sementara --}}
            {{-- <flux:tabs variant="segmented">
                <flux:tab icon="calendar-days" x-on:click="activeTab = 'timeline'"
                    x-bind:data-selected="activeTab === 'timeline'">
                    Timeline
                </flux:tab>
                <flux:tab icon="list-bullet" x-on:click="activeTab = 'tabel'"
                    x-bind:data-selected="activeTab === 'tabel'">
                    Tabel
                </flux:tab>
            </flux:tabs> --}}

            <div class="flex items-center flex-wrap gap-3">
                <x-select wire:model.live="filterData.hari" :search="false"
                    :options="JadwalHelper::getHariOptions(true)" placeholder="Pilih hari" class="!w-[130px]" />
                <x-select wire:model.live="filterData.tingkat" :search="false" :options="[['label' => 'SMP', 'value' => 'smp'], ['label' => 'MA', 'value' => 'ma']]" placeholder="Pilih tingkat" class="!w-[110px]" />
                <x-select wire:model.live="filterData.guru_id" :search="true"
                    :options="JadwalHelper::getGuruOptions(true)" placeholder="Filter Guru..." class="!w-[220px]" />
            </div>
        </div>

        <div class="mt-4 min-h-[600px]">
            {{-- Tampilan tabel disembunyikan sementara --}}
            {{-- <div x-cloak x-show="activeTab === 'tabel'">
                <livewire:datatable.jadwal lazy :periode_id="$this->periode_id" />
            </div> --}}
            <div>
                <livewire:datatable.jadwal-matrix lazy :periode_id="$this->periode_id" :hari="$this->filterData['hari']"
                    :tingkat="$this->filterData['tingkat']" :guru_id="$this->filterData['guru_id']" wire:key="matrix-{{ md5(json_encode($filterData)) }}" />
            </div>
        </div>
    </div>

    <x-filament-actions::modals />

    {{-- Add Data Modal --}}
    <flux:modal name="jadwal-modal" class="w-[90%] md:w-[480px] z-[30]" scroll="null" variant="flyout">
        <form wire:submit.prevent="save" class="flex flex-col gap-3 max-w-[768px]">
            <flux:heading size="lg">
                {{ $isEdit ? 'Ubah Data Jadwal' : 'Tambah Data Jadwal' }}
            </flux:heading>

            @php
                $selectedKelasNama = collect($kelasOptions)->firstWhere('value', $this->formData['kelas_id'] ?? null)['label'] ?? ($this->formData['kelas'] ?? '-');
                $selectedHari = !empty($this->formData['hari']) ? ucfirst($this->formData['hari']) : '-';
            @endphp

            <div class="flex items-center justify-between gap-3 bg-gray-100 dark:bg-gray-800/80 px-3.5 py-2 rounded-xl text-xs md:text-sm font-semibold border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200">
                <div class="flex items-center gap-1.5">
                    <flux:icon name="calendar-days" class="w-4 h-4 text-primary shrink-0" />
                    <span>Hari: <strong class="text-gray-900 dark:text-white">{{ $selectedHari }}</strong></span>
                </div>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <div class="flex items-center gap-1.5">
                    <flux:icon name="academic-cap" class="w-4 h-4 text-primary shrink-0" />
                    <span>Kelas: <strong class="text-gray-900 dark:text-white">{{ $selectedKelasNama }}</strong></span>
                </div>
            </div>

            @if (count($this->jadwalBentrokList) >= 1)
                <div class="space-y-3 bg-red-50 dark:bg-red-950/40 p-3.5 rounded-xl border border-red-200 dark:border-red-900/50">
                    <div class="flex items-start gap-2.5 text-red-700 dark:text-red-300">
                        <flux:icon name="exclamation-triangle" class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                        <div>
                            <strong class="font-bold text-sm">Terjadi Bentrok Jadwal:</strong>
                            <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                                @foreach ($this->jadwalBentrokList as $jadwal)
                                    <li>
                                        {{ $jadwal['kelas'] }} ({{ $jadwal['jam_mulai'] }} - {{ $jadwal['jam_selesai'] }}) : {{ $jadwal['guru'] }} - {{ $jadwal['mapel'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Selector Guru diatas sendiri --}}
            <flux:field>
                <flux:label>Guru Pengajar</flux:label>
                <x-select name="formData.guru_id" wire:model="formData.guru_id" :options="$this->guruOptions"
                    placeholder="Pilih guru..." clearable />
                <flux:error name="formData.guru_id" />
            </flux:field>

            <flux:field>
                <flux:label>Nama Mata Pelajaran</flux:label>
                <livewire:mapel-option wire:model="formData.mata_pelajaran_id" :periodeId="$this->periode_id"
                    placeholder="Pilih mata pelajaran..." />
                <flux:error name="formData.mata_pelajaran_id" />
            </flux:field>

            @php
                $availIds = collect($this->availableSlotsList)->pluck('id')->map('strval')->toArray();
                $hasBentrok = count($this->jadwalBentrokList) > 0;
                $hasSlotInfo = !empty($this->availableSlotsList) || $hasBentrok;
            @endphp

            <flux:field>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <flux:label>Jam ke (Bisa pilih beberapa jam sekaligus)</flux:label>
                    @if ($hasSlotInfo)
                        <div class="flex items-center gap-2 text-[11px] font-semibold">
                            <span class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/80 px-2 py-0.5 rounded-full border border-emerald-300 dark:border-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Tersedia</span>
                            </span>
                            @if ($hasBentrok)
                                <span class="inline-flex items-center gap-1 text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-950/80 px-2 py-0.5 rounded-full border border-red-300 dark:border-red-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span>Bentrok</span>
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-52 overflow-y-auto p-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900">
                    @foreach ($this->jamPelajaranOptions as $jamOpt)
                        @php
                            $sId = (string) $jamOpt['value'];
                            $isAvailableSlot = in_array($sId, $availIds);
                            $isBentrokSlot = $hasBentrok && in_array($sId, array_map('strval', $this->formData['jam_pelajaran_ids'] ?? [])) && !$isAvailableSlot;
                        @endphp
                        <label
                            class="flex items-center justify-between gap-2 p-2.5 rounded-lg cursor-pointer text-xs md:text-sm border transition shadow-2xs {{ $isBentrokSlot ? 'bg-red-50 dark:bg-red-950/50 border-red-300 dark:border-red-800 text-red-900 dark:text-red-200 ring-1 ring-red-400' : ($isAvailableSlot ? 'bg-emerald-50/80 dark:bg-emerald-950/50 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 hover:bg-emerald-100 dark:hover:bg-emerald-900/70' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200') }}">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" wire:model="formData.jam_pelajaran_ids" value="{{ $jamOpt['value'] }}"
                                    class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="font-medium">{{ $jamOpt['label'] }}</span>
                            </div>
                            @if ($isBentrokSlot)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-red-600 text-white shadow-xs shrink-0 flex items-center gap-1">
                                    <flux:icon name="x-circle" class="w-3 h-3" />
                                    <span>Bentrok</span>
                                </span>
                            @elseif ($isAvailableSlot)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-600 text-white shadow-xs shrink-0 flex items-center gap-1">
                                    <flux:icon name="check-circle" class="w-3 h-3" />
                                    <span>Tersedia</span>
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
                <flux:error name="formData.jam_pelajaran_ids" />
            </flux:field>



            <div class="flex mt-8">
                @if ($this->isEdit)
                    <flux:button variant="primary" color="red" icon="trash" x-on:click="() => {
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

    {{-- Modal Peringatan & Alokasi Solusi Bentrok Jadwal --}}
    <flux:modal name="jadwal-bentrok-modal" class="w-[90%] md:w-[620px] z-[50]" scroll="null">
        <div class="space-y-4 p-1">
            <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
                <flux:icon name="exclamation-triangle" class="w-7 h-7 shrink-0 text-red-500" />
                <div>
                    <flux:heading size="lg" class="text-red-700 dark:text-red-400 font-bold">Resolusi Bentrok Jadwal</flux:heading>
                    <flux:subheading size="sm">Pilih slot jam kosong yang diinginkan atau gunakan alokasi otomatis.</flux:subheading>
                </div>
            </div>

            @php
                $takenSlotsMap = $this->getTakenSlotsMap();
            @endphp
            <div class="space-y-3 max-h-[340px] overflow-y-auto pr-1">
                @foreach ($this->jadwalBentrokList as $idx => $item)
                    <div class="bg-red-50/70 dark:bg-red-950/40 p-3.5 rounded-xl border border-red-200/80 dark:border-red-900/50 space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-xs font-bold text-red-800 dark:text-red-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span>{{ $item['kelas'] ?? 'Kelas' }}</span>
                                    <span class="font-normal text-gray-600 dark:text-gray-400">({{ $item['hari'] ?? '' }}, {{ $item['jam_mulai'] ?? '' }} - {{ $item['jam_selesai'] ?? '' }})</span>
                                </div>
                                <div class="text-xs text-gray-700 dark:text-gray-300 mt-1">
                                    Mapel: <strong>{{ $item['mapel'] ?? '-' }}</strong> | Guru: <strong>{{ $item['guru'] ?? '-' }}</strong>
                                </div>
                            </div>
                            @if (!empty($item['selected_slot_id']))
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 flex items-center gap-1">
                                    <flux:icon name="check-circle" class="w-3 h-3 text-emerald-600" />
                                    <span>Jam Terpilih</span>
                                </span>
                            @endif
                        </div>

                        {{-- Day Selector & Selection Pills for Available Empty Slots --}}
                        <div class="pt-2 border-t border-red-200/60 dark:border-red-900/40 space-y-2">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="text-[11px] font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1">
                                    <flux:icon name="sparkles" class="w-3.5 h-3.5 text-amber-500" />
                                    <span>Pilih Hari & Slot Kosong:</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <label class="text-[11px] font-medium text-gray-600 dark:text-gray-400">Hari:</label>
                                    <select wire:change="changeHariForBentrokItem({{ $idx }}, $event.target.value)"
                                        class="text-xs px-2 py-0.5 rounded-md bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 font-semibold focus:ring-1 focus:ring-primary shadow-xs">
                                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hariOpt)
                                            <option value="{{ $hariOpt }}" {{ ($item['selected_hari'] ?? '') == $hariOpt ? 'selected' : '' }}>
                                                {{ $hariOpt }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @if (!empty($item['available_slots']) && count($item['available_slots']) > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($item['available_slots'] as $slot)
                                        @php
                                            $sId = (string) $slot['id'];
                                            $h = $item['selected_hari'] ?? 'Senin';
                                            $kId = $item['data']['kelas_id'] ?? null;
                                            $gId = $item['data']['guru_id'] ?? null;

                                            $isSelected = ($item['selected_slot_id'] ?? null) == $sId;
                                            $isTakenByOther = !$isSelected && (
                                                isset($takenSlotsMap["{$h}_{$sId}"]) ||
                                                ($kId && isset($takenSlotsMap["{$h}_{$sId}_k_{$kId}"])) ||
                                                ($gId && isset($takenSlotsMap["{$h}_{$sId}_g_{$gId}"]))
                                            );
                                        @endphp

                                        @if ($isTakenByOther)
                                            <button type="button" disabled
                                                class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-200/80 dark:bg-gray-800/80 text-gray-400 dark:text-gray-500 border border-gray-300/80 dark:border-gray-700/80 cursor-not-allowed flex items-center gap-1"
                                                title="Slot ini sudah dipilih untuk alokasi jadwal lain">
                                                <flux:icon name="lock-closed" class="w-3 h-3 text-gray-400 dark:text-gray-500" />
                                                <span>{{ $slot['label'] }} (Terpakai)</span>
                                            </button>
                                        @else
                                            <button type="button" wire:click="selectSlotForBentrokItem({{ $idx }}, '{{ $sId }}')"
                                                class="px-2.5 py-1 rounded-md text-xs font-semibold transition flex items-center gap-1 border {{ $isSelected ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-800 dark:text-emerald-200 border-emerald-300 dark:border-emerald-800' }}">
                                                @if($isSelected)
                                                    <flux:icon name="check-circle" class="w-3.5 h-3.5 text-white" />
                                                @else
                                                    <flux:icon name="plus-circle" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                                @endif
                                                <span>{{ $slot['label'] }}</span>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-[11px] text-red-600 dark:text-red-400 italic">
                                    Tidak ada slot kosong yang tersedia pada hari <strong>{{ $item['selected_hari'] ?? 'ini' }}</strong>. Silakan pilih hari lain.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-gray-200 dark:border-gray-800">
                <flux:modal.close>
                    <flux:button variant="ghost" size="sm">Tutup</flux:button>
                </flux:modal.close>

                <div class="flex items-center gap-2">
                    <flux:button type="button" wire:click="autoResolveBentrok" variant="outline" size="sm" icon="sparkles" class="!text-emerald-700 dark:!text-emerald-300">
                        Alokasi Otomatis Semua
                    </flux:button>

                    <flux:button type="button" wire:click="saveBentrokAllocations" variant="filled" color="emerald" size="sm" class="!bg-emerald-600 hover:!bg-emerald-700 !text-white">
                        Simpan Alokasi Jadwal
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>