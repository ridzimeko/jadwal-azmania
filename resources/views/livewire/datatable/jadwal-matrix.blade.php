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
            ->orderBy('urutan')
            ->orderBy('jam_mulai')
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

    #[On('refreshJadwalTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }
};

?>

<div class="w-full space-y-4">
    @php
        $kelasList = $this->getKelas();
        $jamList = $this->getJamPelajaran();
        $hariList = $this->getHariList();
        $jadwalMap = $this->getJadwalMap();

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
    <div class="flex items-center justify-between gap-4 flex-wrap bg-gray-50 dark:bg-gray-800/60 p-4 rounded-lg border border-gray-200 dark:border-gray-700 text-sm md:text-base">
        <div class="flex items-center gap-6">
            <span class="flex items-center gap-2 font-medium text-gray-700 dark:text-gray-200">
                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                Terisi: <strong class="text-base md:text-lg text-gray-900 dark:text-white">{{ $totalTerisi }}</strong> slot
            </span>
            <span class="flex items-center gap-2 font-medium text-gray-700 dark:text-gray-200">
                <span class="w-3 h-3 rounded-full bg-amber-400 inline-block animate-pulse"></span>
                Kosong: <strong class="text-base md:text-lg text-amber-600 dark:text-amber-400">{{ $totalKosong }}</strong> slot
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="$toggle('onlyEmpty')" wire:loading.attr="disabled"
                class="px-3 py-1.5 rounded-md text-xs font-semibold border transition flex items-center gap-1.5 disabled:opacity-70 {{ $onlyEmpty ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700 hover:bg-gray-100' }}">
                <flux:icon name="arrow-path" wire:loading wire:target="$toggle('onlyEmpty')" class="w-3.5 h-3.5 animate-spin text-current" />
                <flux:icon name="funnel" wire:loading.remove wire:target="$toggle('onlyEmpty')" class="w-3.5 h-3.5" />
                <span>{{ $onlyEmpty ? 'Tampilkan Semua Slot' : 'Highlight Slot Kosong Only' }}</span>
            </button>
        </div>
    </div>

    <!-- Matrix Grid Table -->
    <div class="w-full overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-xs">
        @if (count($jamList) >= 1 && count($kelasList) >= 1)
            <table class="min-w-full border-collapse text-xs md:text-sm">
                <thead class="bg-primary text-white">
                    @if ($this->hari)
                        <tr>
                            <th colspan="{{ 2 + count($kelasList) }}" class="bg-amber-400 text-gray-900 font-bold px-4 py-2 border border-amber-500 text-center uppercase tracking-wider">
                                Hari {{ $this->hari }}
                            </th>
                        </tr>
                    @endif
                    <tr>
                        <th class="px-3 py-2 border border-primary-600 text-center w-12">No</th>
                        @if (!$this->hari)
                            <th class="px-3 py-2 border border-primary-600 text-center w-24">Hari</th>
                        @endif
                        <th class="px-3 py-2 border border-primary-600 text-center w-36 sticky left-0 z-10 bg-primary text-white font-semibold">Jam ke / Waktu</th>
                        @foreach ($kelasList as $kelas)
                            <th class="px-3 py-2 border border-primary-600 text-center min-w-[130px] font-semibold">{{ $kelas->nama_kelas }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @php $no = 1; @endphp
                    @foreach ($hariList as $hariKey)
                        @foreach ($jamList as $jam)
                            @php
                                $jamLabel = "Jam {$jam->urutan} ({$jam->jam_mulai} - {$jam->jam_selesai})";
                                // Cek apakah ada jadwal global (SMP / MA) di jam & hari ini
                                $globalItems = collect();
                                foreach (['SMP', 'MA'] as $globalKode) {
                                    $gKelas = \App\Models\Kelas::where('kode_kelas', $globalKode)->first();
                                    if ($gKelas) {
                                        $gKey = $hariKey . '_' . $jam->id . '_' . $gKelas->id;
                                        if (!empty($jadwalMap[$gKey])) {
                                            $globalItems = $globalItems->concat($jadwalMap[$gKey]);
                                        }
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-3 py-2 border text-center text-gray-500 font-medium">{{ $no++ }}</td>

                                @if (!$this->hari)
                                    <td class="px-3 py-2 border text-center font-medium text-gray-700 dark:text-gray-300 bg-gray-50/30 dark:bg-gray-800/20">{{ $hariKey }}</td>
                                @endif

                                <td class="px-3 py-2 border text-center font-medium sticky left-0 z-10 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 whitespace-nowrap shadow-xs">
                                    {{ $jamLabel }}
                                </td>

                                {{-- Jika ada kegiatan Global (SMP / MA) --}}
                                @if ($globalItems->count() > 0)
                                    <td colspan="{{ count($kelasList) }}"
                                        class="px-3 py-2 border text-center align-middle bg-purple-50 dark:bg-purple-950/30">
                                        @foreach ($globalItems as $item)
                                            @php
                                                $bg = $item->guru->warna ?? '#8b5cf6';
                                                $text = \App\Helpers\ColorHelper::getTextColor($bg);
                                            @endphp
                                            <button class="w-full p-2 rounded-md font-semibold text-center shadow-xs transition hover:brightness-95"
                                                style="background-color: {{ $bg }}; color: {{ $text }}" 
                                                wire:click="$parent.openEditJadwal({{ json_encode([
                                                    'id' => $item->id,
                                                    'hari' => $hariKey,
                                                    'kelas_id' => $item->kelas_id,
                                                    'mata_pelajaran_id' => $item->mata_pelajaran_id,
                                                    'jam_pelajaran_id' => $item->jam_pelajaran_id,
                                                    'guru_id' => $item->guru_id,
                                                ]) }})">
                                                <div>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</div>
                                                <div class="text-xs opacity-90">{{ $item->guru->nama_guru ?? 'Semua Kelas' }}</div>
                                            </button>
                                        @endforeach
                                    </td>
                                @else
                                    {{-- Kolom Per Kelas --}}
                                    @foreach ($kelasList as $kelas)
                                        @php
                                            $cellKey = $hariKey . '_' . $jam->id . '_' . $kelas->id;
                                            $kelasItems = $jadwalMap[$cellKey] ?? [];
                                            $isEmpty = empty($kelasItems);
                                        @endphp
                                        <td class="px-2 py-2 border text-center align-top transition-all {{ $isEmpty ? ($onlyEmpty ? 'bg-amber-100 dark:bg-amber-900/50 border-amber-400 ring-2 ring-amber-400/80 shadow-md' : 'bg-gray-50/40 dark:bg-gray-800/10') : ($onlyEmpty ? 'opacity-30 grayscale' : '') }}">
                                            @if (!$isEmpty)
                                                @foreach ($kelasItems as $item)
                                                    @php
                                                        $bg = $item->guru->warna ?? '#ffffff';
                                                        $text = \App\Helpers\ColorHelper::getTextColor($bg);
                                                        $isBentrok = $item->is_bentrok ?? false;
                                                    @endphp
                                                    <button class="w-full mb-1.5 p-2 rounded-md shadow-xs text-left cursor-pointer transition hover:scale-[1.02] border {{ $isBentrok ? 'border-red-500 ring-2 ring-red-400' : 'border-black/10' }}"
                                                        style="background-color: {{ $bg }}; color: {{ $text }}" 
                                                        wire:click="$parent.openEditJadwal({{ json_encode([
                                                            'id' => $item->id,
                                                            'hari' => $hariKey,
                                                            'kelas_id' => $kelas->id,
                                                            'mata_pelajaran_id' => $item->mata_pelajaran_id,
                                                            'jam_pelajaran_id' => $jam->id,
                                                            'guru_id' => $item->guru_id,
                                                        ]) }})">
                                                        <div class="font-bold text-xs md:text-sm line-clamp-1">{{ $item->mataPelajaran->nama_mapel ?? '-' }}</div>
                                                        <div class="text-[11px] opacity-90 line-clamp-1 mt-0.5">{{ $item->guru->nama_guru ?? 'Tanpa Guru' }}</div>
                                                        @if ($isBentrok)
                                                            <div class="text-[10px] bg-red-600 text-white font-bold px-1 rounded mt-1 inline-block">BENTROK!</div>
                                                        @endif
                                                    </button>
                                                @endforeach
                                            @else
                                                {{-- TAMPILAN SLOT KOSONG --}}
                                                <button type="button" 
                                                    wire:click="$parent.openEditJadwal({{ json_encode([
                                                        'hari' => $hariKey,
                                                        'kelas_id' => $kelas->id,
                                                        'mata_pelajaran_id' => '',
                                                        'jam_pelajaran_id' => $jam->id,
                                                        'guru_id' => '',
                                                    ]) }})"
                                                    class="group w-full py-3 px-2 rounded-md border-2 border-dashed transition-all flex flex-col items-center justify-center gap-1 cursor-pointer {{ $onlyEmpty ? 'border-amber-500 bg-amber-200/80 dark:bg-amber-900/60 text-amber-900 dark:text-amber-100 font-bold animate-pulse shadow-sm' : 'border-gray-300 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-400 hover:bg-emerald-50/70 dark:hover:bg-emerald-950/30 text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                                                    <flux:icon name="plus" class="w-4 h-4 group-hover:scale-125 transition-transform" />
                                                    <span class="text-[11px] font-semibold tracking-tight">{{ $onlyEmpty ? 'KOSONG' : 'Kosong' }}</span>
                                                </button>
                                            @endif
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="w-full rounded-md py-16 flex flex-col gap-3 items-center justify-center text-center bg-gray-50 dark:bg-gray-800">
                <flux:icon name="question-mark-circle" class="size-12 text-gray-400" />
                <p class="font-semibold text-gray-600 dark:text-gray-300">Belum ada master Jam Pelajaran atau Kelas yang diset.</p>
            </div>
        @endif
    </div>
</div>