<?php

use App\Helpers\JadwalHelper;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $periode_id;

    public string $tingkat = 'SMP'; // bisa di-pass lewat route/filter

    public $hari;

    public $guru_id = null;

    public bool $onlyEmpty = false;

    public function placeholder()
    {
        return view('components.loading');
    }

    #[Computed]
    public function getKelas()
    {
        $kelasQuery = Kelas::query()->orderBy('nama_kelas');
        if ($this->tingkat) {
            $kelasQuery->where('tingkat', strtoupper($this->tingkat));
        }
        return $kelasQuery
            ->whereNotIn('kode_kelas', ['SMP', 'MA'])
            ->get();
    }

    #[Computed]
    public function getJamPelajaran()
    {
        return JamPelajaran::query()
            ->orderBy('jam_mulai')
            ->orderBy('urutan')
            ->get();
    }

    #[Computed]
    public function getHariList()
    {
        if ($this->hari) {
            return [$this->hari];
        }
        return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    }

    #[Computed]
    public function getJadwalMap()
    {
        $query = JadwalHelper::getQuery($this->periode_id, strtoupper($this->tingkat));

        if ($this->hari) {
            $query->where('hari', $this->hari);
        }

        if ($this->guru_id) {
            $query->where('guru_id', $this->guru_id);
        }

        $items = $query->get();

        $map = [];
        foreach ($items as $item) {
            $key = $item->hari . '_' . $item->jam_pelajaran_id . '_' . ($item->kelas_id ?? 'global');
            if (!isset($map[$key])) {
                $map[$key] = [];
            }
            $map[$key][] = $item;
        }

        return $map;
    }

    #[Computed]
    public function getGlobalKelasMap()
    {
        return Kelas::whereIn('kode_kelas', ['SMP', 'MA'])->pluck('id', 'kode_kelas')->toArray();
    }

    #[On('refreshJadwalTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }
};

?>

<div class="w-full space-y-4"
    x-data="{
        isGuru: {{ auth()->user()->role === 'guru' ? 'true' : 'false' }},
        isDragging: false,
        dragHari: '',
        dragKelasId: null,
        startJamId: null,
        selectedJams: [],

        openModal(record) {
            if (this.isGuru) return;
            const currentScrollY = window.scrollY;
            $wire.$parent.openEditJadwal(record).then(() => {
                requestAnimationFrame(() => {
                    window.scrollTo({ top: currentScrollY, behavior: 'instant' });
                });
            });
        },

        startDrag(hari, kelasId, jamId) {
            if (this.isGuru) return;
            this.isDragging = true;
            this.dragHari = hari;
            this.dragKelasId = kelasId;
            this.startJamId = jamId;
            this.selectedJams = [jamId];
        },

        dragOver(hari, kelasId, jamId, allJamIds) {
            if (!this.isDragging) return;
            if (this.dragHari !== hari || this.dragKelasId !== kelasId) return;

            const startIndex = allJamIds.indexOf(this.startJamId);
            const currentIndex = allJamIds.indexOf(jamId);

            if (startIndex !== -1 && currentIndex !== -1) {
                const min = Math.min(startIndex, currentIndex);
                const max = Math.max(startIndex, currentIndex);
                this.selectedJams = allJamIds.slice(min, max + 1);
            }
        },

        endDrag() {
            if (!this.isDragging) return;
            this.isDragging = false;
            if (this.selectedJams.length > 0) {
                const jams = this.selectedJams.map(String);
                this.openModal({
                    hari: this.dragHari,
                    kelas_id: this.dragKelasId,
                    mata_pelajaran_id: '',
                    jam_pelajaran_id: '',
                    jam_pelajaran_ids: jams,
                    guru_id: ''
                });
            }
            this.selectedJams = [];
        },

        isSelected(hari, kelasId, jamId) {
            return this.dragHari === hari && this.dragKelasId === kelasId && this.selectedJams.includes(jamId);
        }
    }"
    @mouseup.window="endDrag()"
>
    @php
        $kelasList = $this->getKelas();
        $jamList = $this->getJamPelajaran();
        $hariList = $this->getHariList();
        $jadwalMap = $this->getJadwalMap();
        $globalKelasMap = $this->getGlobalKelasMap();
        $allJamIds = $jamList->pluck('id')->values()->toArray();

        // Hitung statistik slot
        $totalSlot = count($hariList) * count($jamList) * count($kelasList);
        $totalTerisi = 0;

        foreach ($hariList as $h) {
            foreach ($jamList as $j) {
                foreach ($kelasList as $k) {
                    $key = $h . '_' . $j->id . '_' . $k->id;
                    if (!empty($jadwalMap[$key])) {
                        $totalTerisi++;
                    }
                }
            }
        }
        $totalKosong = max(0, $totalSlot - $totalTerisi);
    @endphp

    <!-- Header Stats & Indicator Bar -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between gap-4 flex-wrap bg-gray-50 dark:bg-gray-800/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700 text-xs md:text-sm">
            <div class="flex items-center gap-5">
                <span class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-200">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                    Terisi: <strong class="text-xs md:text-sm font-bold text-gray-900 dark:text-white">{{ $totalTerisi }}</strong> slot
                </span>
                <span class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-200">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block animate-pulse"></span>
                    Kosong: <strong class="text-xs md:text-sm font-bold text-amber-600 dark:text-amber-400">{{ $totalKosong }}</strong> slot
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="$toggle('onlyEmpty')" wire:loading.attr="disabled"
                    class="px-3 py-1 rounded-lg text-xs font-semibold border transition flex items-center gap-1.5 disabled:opacity-70 {{ $onlyEmpty ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700 hover:bg-gray-100' }}">
                    <flux:icon name="arrow-path" wire:loading wire:target="$toggle('onlyEmpty')" class="w-3.5 h-3.5 animate-spin text-current" />
                    <flux:icon name="funnel" wire:loading.remove wire:target="$toggle('onlyEmpty')" class="w-3.5 h-3.5" />
                    <span>{{ $onlyEmpty ? 'Tampilkan Semua Slot' : 'Highlight Slot Kosong Only' }}</span>
                </button>
            </div>
        </div>

        @if(auth()->user()->role !== 'guru')
            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 bg-blue-50/60 dark:bg-blue-950/30 px-3.5 py-1.5 rounded-lg border border-blue-100 dark:border-blue-900/40">
                <flux:icon name="cursor-arrow-rays" class="w-3.5 h-3.5 text-primary shrink-0" />
                <span>Tips: <strong>Klik & drag</strong> slot kosong berurutan di dalam matriks untuk memilih beberapa jam pelajaran sekaligus</span>
            </div>
        @endif
    </div>

    <!-- Matrix Grid Table -->
    <div class="w-full overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs">
        @if (count($jamList) >= 1 && count($kelasList) >= 1)
            <table class="min-w-full border-separate border-spacing-0 text-xs md:text-sm select-none">
                <colgroup>
                    <col class="w-12 min-w-[48px]">
                    <col class="w-36 min-w-[145px]">
                    @foreach ($kelasList as $kelas)
                        <col class="min-w-[160px]">
                    @endforeach
                </colgroup>
                <thead class="bg-primary text-white sticky top-0 z-30 shadow-sm">
                    @if ($this->hari)
                        <tr>
                            <th colspan="{{ 2 + count($kelasList) }}" class="bg-amber-400 text-gray-900 font-bold px-4 py-1.5 border-b border-amber-500 text-center uppercase tracking-wider text-xs md:text-sm">
                                Hari {{ $this->hari }}
                            </th>
                        </tr>
                    @endif
                    <tr>
                        <th class="px-2 py-2.5 border-b border-r border-primary-600 text-center w-12 min-w-[48px] sticky left-0 z-40 bg-primary">No</th>
                        <th class="px-3 py-2.5 border-b border-r border-primary-600 text-center w-36 min-w-[145px] sticky left-12 z-40 bg-primary font-semibold">Jam ke / Waktu</th>
                        @foreach ($kelasList as $kelas)
                            <th class="px-4 py-2.5 border-b border-r border-primary-600 text-center min-w-[160px] font-semibold last:border-r-0">{{ $kelas->nama_kelas }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900">
                    @php
                        $skipCell = [];
                        $spanCountMap = [];

                        // Pre-calculate consecutive merged cells for each hari & kelas
                        foreach ($hariList as $hKey) {
                            $jamArray = $jamList->values();
                            $totalJams = count($jamArray);
                            for ($i = 0; $i < $totalJams; $i++) {
                                $currentJam = $jamArray[$i];
                                foreach ($kelasList as $kelas) {
                                    if (isset($skipCell[$hKey][$currentJam->id][$kelas->id])) {
                                        continue;
                                    }
                                    $cKey = $hKey . '_' . $currentJam->id . '_' . $kelas->id;
                                    $currentItems = $jadwalMap[$cKey] ?? [];
                                    if (!empty($currentItems)) {
                                        $firstItem = $currentItems[0];
                                        $mapelId = $firstItem->mata_pelajaran_id;
                                        $guruId = $firstItem->guru_id;

                                        $span = 1;
                                        $spanJamIds = [(string) $currentJam->id];
                                        for ($j = $i + 1; $j < $totalJams; $j++) {
                                            $nextJam = $jamArray[$j];
                                            $nextKey = $hKey . '_' . $nextJam->id . '_' . $kelas->id;
                                            $nextItems = $jadwalMap[$nextKey] ?? [];
                                            if (!empty($nextItems) && count($nextItems) === 1 && $nextItems[0]->mata_pelajaran_id == $mapelId && $nextItems[0]->guru_id == $guruId) {
                                                $span++;
                                                $spanJamIds[] = (string) $nextJam->id;
                                                $skipCell[$hKey][$nextJam->id][$kelas->id] = true;
                                            } else {
                                                break;
                                            }
                                        }
                                        $spanCountMap[$hKey][$currentJam->id][$kelas->id] = $span;
                                        $spanJamIdsMap[$hKey][$currentJam->id][$kelas->id] = $spanJamIds;
                                    } else {
                                        $spanCountMap[$hKey][$currentJam->id][$kelas->id] = 1;
                                        $spanJamIdsMap[$hKey][$currentJam->id][$kelas->id] = [(string) $currentJam->id];
                                    }
                                }
                            }
                        }
                    @endphp
                    @foreach ($hariList as $hariKey)
                        @php
                            $no = 1;
                        @endphp

                        {{-- Header Group Category Hari saat Scroll --}}
                        @if (!$this->hari)
                            <tr class="bg-amber-50 dark:bg-slate-800 text-amber-900 dark:text-amber-300 font-bold shadow-xs">
                                <td colspan="{{ 2 + count($kelasList) }}" class="px-4 py-2 text-left sticky left-0 z-20 bg-amber-100/95 dark:bg-slate-800/95 border-b border-amber-200 dark:border-slate-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-2.5 w-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <flux:icon name="calendar-days" class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                            <span class="tracking-wider uppercase text-amber-900 dark:text-amber-300 font-extrabold text-xs md:text-sm">HARI {{ $hariKey }}</span>
                                        </div>
                                        <span class="text-[11px] font-semibold text-amber-800 dark:text-amber-200 bg-amber-200/70 dark:bg-slate-700/80 px-2.5 py-0.5 rounded-full border border-amber-300 dark:border-slate-600">
                                            {{ count($jamList) }} Jam Pelajaran
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        @foreach ($jamList as $jamIndex => $jam)
                            @php
                                // Cek apakah ada jadwal global (SMP / MA) di jam & hari ini
                                $globalItems = collect();
                                foreach (['SMP', 'MA'] as $globalKode) {
                                    $gKelasId = $globalKelasMap[$globalKode] ?? null;
                                    if ($gKelasId) {
                                        $gKey = $hariKey . '_' . $jam->id . '_' . $gKelasId;
                                        if (!empty($jadwalMap[$gKey])) {
                                            $globalItems = $globalItems->concat($jadwalMap[$gKey]);
                                        }
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-2 py-2 border-b border-r border-gray-200 dark:border-gray-700 text-center text-gray-500 font-medium sticky left-0 z-10 bg-white dark:bg-gray-900 w-12 min-w-[48px] shadow-xs">{{ $no++ }}</td>

                                <td class="px-2 py-1.5 border-b border-r border-gray-200 dark:border-gray-700 text-center font-medium sticky left-12 z-10 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 w-36 min-w-[145px] shadow-md">
                                    <div class="font-bold text-xs md:text-sm text-gray-900 dark:text-white">Jam {{ $jam->urutan }}</div>
                                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-0.5">({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})</div>
                                </td>

                                {{-- Jika ada kegiatan Global (SMP / MA) --}}
                                @if ($globalItems->count() > 0)
                                    <td colspan="{{ count($kelasList) }}"
                                        class="p-2 border-b border-r border-gray-200 dark:border-gray-700 text-center align-middle bg-purple-50 dark:bg-purple-950/30">
                                        @foreach ($globalItems as $item)
                                            @php
                                                $bg = $item->guru->warna ?? '#8b5cf6';
                                                $text = \App\Helpers\ColorHelper::getTextColor($bg);
                                            @endphp
                                            <button class="w-full p-2.5 rounded-lg font-semibold text-center shadow-xs transition hover:brightness-95"
                                                style="background-color: {{ $bg }}; color: {{ $text }}" 
                                                @click="openModal({{ json_encode([
                                                    'id' => $item->id,
                                                    'hari' => $hariKey,
                                                    'kelas_id' => $item->kelas_id,
                                                    'mata_pelajaran_id' => $item->mata_pelajaran_id,
                                                    'jam_pelajaran_id' => $item->jam_pelajaran_id,
                                                    'guru_id' => $item->guru_id,
                                                ]) }})">
                                                <div class="font-bold text-sm">{{ $item->mataPelajaran->nama_mapel ?? '-' }}</div>
                                                <div class="text-xs opacity-90 mt-0.5">{{ $item->guru->nama_guru ?? 'Semua Kelas' }}</div>
                                            </button>
                                        @endforeach
                                    </td>
                                @else
                                    {{-- Kolom Per Kelas --}}
                                    @foreach ($kelasList as $kelas)
                                        @php
                                            $isSkipped = $skipCell[$hariKey][$jam->id][$kelas->id] ?? false;
                                        @endphp
                                        @if (!$isSkipped)
                                            @php
                                                $rowSpan = $spanCountMap[$hariKey][$jam->id][$kelas->id] ?? 1;
                                                $cellKey = $hariKey . '_' . $jam->id . '_' . $kelas->id;
                                                $kelasItems = $jadwalMap[$cellKey] ?? [];
                                                $isEmpty = empty($kelasItems);
                                            @endphp
                                            <td rowspan="{{ $rowSpan }}" class="h-1 p-2 border-b border-r border-gray-200 dark:border-gray-700 text-center align-middle transition-all {{ $isEmpty ? ($onlyEmpty ? 'bg-amber-100 dark:bg-amber-900/50 border-amber-400 ring-2 ring-amber-400/80 shadow-md' : 'bg-gray-50/40 dark:bg-gray-800/10') : ($onlyEmpty ? 'opacity-30 grayscale' : '') }}">
                                                <div class="h-full w-full flex flex-col justify-center items-center gap-1.5 p-0.5">
                                                    @if (!$isEmpty)
                                                        @foreach ($kelasItems as $item)
                                                            @php
                                                                $bg = $item->guru->warna ?? '#ffffff';
                                                                $text = \App\Helpers\ColorHelper::getTextColor($bg);
                                                                $isBentrok = $item->is_bentrok ?? false;
                                                            @endphp
                                                            <button class="w-full h-full min-h-[58px] p-2.5 rounded-xl shadow-xs text-center flex flex-col justify-center items-center cursor-pointer transition hover:scale-[1.01] hover:shadow-md border {{ $isBentrok ? 'border-red-500 ring-2 ring-red-400' : 'border-black/10' }}"
                                                                style="background-color: {{ $bg }}; color: {{ $text }}" 
                                                                @click="openModal({{ json_encode([
                                                                    'id' => $item->id,
                                                                    'hari' => $hariKey,
                                                                    'kelas_id' => $kelas->id,
                                                                    'mata_pelajaran_id' => $item->mata_pelajaran_id,
                                                                    'jam_pelajaran_id' => $jam->id,
                                                                    'jam_pelajaran_ids' => $spanJamIdsMap[$hariKey][$jam->id][$kelas->id] ?? [(string) $jam->id],
                                                                    'guru_id' => $item->guru_id,
                                                                ]) }})">
                                                                <div class="font-bold text-xs md:text-sm line-clamp-2 leading-tight px-1">{{ $item->mataPelajaran->nama_mapel ?? '-' }}</div>
                                                                <div class="text-[11px] opacity-90 line-clamp-1 mt-1 font-medium px-1">{{ $item->guru->nama_guru ?? 'Tanpa Guru' }}</div>
                                                                @if ($rowSpan > 1)
                                                                    <div class="text-[10px] font-semibold px-2 py-0.5 rounded-md mt-1.5 inline-block bg-black/20 text-white shadow-xs">
                                                                        {{ $rowSpan }} JP
                                                                    </div>
                                                                @endif
                                                                @if ($isBentrok)
                                                                    <div class="text-[10px] bg-red-600 text-white font-bold px-1.5 py-0.5 rounded mt-1 inline-block">BENTROK!</div>
                                                                @endif
                                                            </button>
                                                        @endforeach
                                                    @else
                                                        {{-- TAMPILAN SLOT KOSONG --}}
                                                        <button type="button"
                                                            @mousedown.prevent="startDrag('{{ $hariKey }}', {{ $kelas->id }}, {{ $jam->id }})"
                                                            @mouseenter="dragOver('{{ $hariKey }}', {{ $kelas->id }}, {{ $jam->id }}, {{ json_encode($allJamIds) }})"
                                                            :class="isSelected('{{ $hariKey }}', {{ $kelas->id }}, {{ $jam->id }})
                                                                ? 'border-emerald-500 bg-emerald-500 text-white font-bold shadow-md scale-[1.02] ring-2 ring-emerald-400'
                                                                : '{{ $onlyEmpty ? 'border-amber-500 bg-amber-200/80 dark:bg-amber-900/60 text-amber-900 dark:text-amber-100 font-bold animate-pulse shadow-sm' : 'border-gray-300 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-400 hover:bg-emerald-50/70 dark:hover:bg-emerald-950/30 text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400' }}'"
                                                            class="group w-full h-full py-3 px-2 rounded-xl border-2 border-dashed transition-all flex flex-col items-center justify-center gap-1 cursor-pointer select-none">
                                                            <template x-if="isSelected('{{ $hariKey }}', {{ $kelas->id }}, {{ $jam->id }})">
                                                                <div class="flex items-center gap-1">
                                                                    <flux:icon name="check-circle" class="w-4 h-4 text-white animate-bounce" />
                                                                    <span class="text-xs font-bold text-white">TERPILIH</span>
                                                                </div>
                                                            </template>
                                                            <template x-if="!isSelected('{{ $hariKey }}', {{ $kelas->id }}, {{ $jam->id }})">
                                                                <div class="flex flex-col items-center justify-center gap-1">
                                                                    <flux:icon name="plus" class="w-4 h-4 group-hover:scale-125 transition-transform" />
                                                                    <span class="text-[11px] font-semibold tracking-tight">{{ $onlyEmpty ? 'KOSONG' : 'Kosong' }}</span>
                                                                </div>
                                                            </template>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="w-full rounded-xl py-16 flex flex-col gap-3 items-center justify-center text-center bg-gray-50 dark:bg-gray-800">
                <flux:icon name="question-mark-circle" class="size-12 text-gray-400" />
                <p class="font-semibold text-gray-600 dark:text-gray-300">Belum ada master Jam Pelajaran atau Kelas yang diset.</p>
            </div>
        @endif
    </div>
</div>