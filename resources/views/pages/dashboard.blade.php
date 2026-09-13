<?php

use Livewire\Component;

new class extends Component {
    public $totalMataPelajaran;
    public $totalKelas;
    public $totalGuru;
    public $totalUsers;
    public $totalJadwal;
    public $periode;
    public string $selectedHari = 'Senin';

    public function mount()
    {
        if (auth()->user()->role === 'guru') {
            return redirect()->route('jadwal.periode');
        }

        $this->totalMataPelajaran = \App\Models\MataPelajaran::count();
        $this->totalKelas = \App\Models\Kelas::count();
        $this->totalGuru = \App\Models\Guru::count();
        $this->totalUsers = \App\Models\User::count();
        $this->periode = \App\Models\Periode::where('aktif', true)->first() ?? \App\Models\Periode::latest()->first();
        
        $this->totalJadwal = $this->periode 
            ? \App\Models\JadwalPelajaran::where('periode_id', $this->periode->id)->count() 
            : 0;

        $dayNameMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Senin',
        ];
        $currentEngDay = date('l');
        $this->selectedHari = $dayNameMap[$currentEngDay] ?? 'Senin';
    }

    public function selectHari(string $hari)
    {
        $this->selectedHari = $hari;
    }

    #[\Livewire\Attributes\Computed]
    public function getTodayScheduleProperty()
    {
        if (!$this->periode) return collect();

        $jamList = \App\Models\JamPelajaran::orderBy('jam_mulai')->get();
        $jadwals = \App\Models\JadwalPelajaran::where('periode_id', $this->periode->id)
            ->where('hari', $this->selectedHari)
            ->with(['kelas', 'mataPelajaran', 'guru', 'jamPelajaran'])
            ->get();

        $result = collect();
        $currentTime = date('H:i');

        foreach ($jamList as $jam) {
            $items = $jadwals->where('jam_pelajaran_id', $jam->id);
            $isLive = false;
            if ($this->selectedHari === date('l') && $jam->jam_mulai && $jam->jam_selesai) {
                $isLive = ($currentTime >= $jam->jam_mulai && $currentTime <= $jam->jam_selesai);
            }

            $result->push([
                'jam' => $jam,
                'items' => $items,
                'is_live' => $isLive,
            ]);
        }

        return $result;
    }

    #[\Livewire\Attributes\Computed]
    public function getGuruLoadListProperty()
    {
        if (!$this->periode) return collect();

        return \App\Models\JadwalPelajaran::where('periode_id', $this->periode->id)
            ->selectRaw('guru_id, count(*) as total_jp')
            ->whereNotNull('guru_id')
            ->groupBy('guru_id')
            ->orderByDesc('total_jp')
            ->take(6)
            ->with('guru')
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function getRecentLogsProperty()
    {
        return \App\Models\ActivityLog::with('user')->latest()->take(5)->get();
    }

    public ?\App\Models\ActivityLog $selectedLog = null;

    public function viewLogDetail(int $logId)
    {
        $this->selectedLog = \App\Models\ActivityLog::find($logId);
        if ($this->selectedLog) {
            \Flux\Flux::modal('dashboard-log-detail-modal')->show();
        }
    }

    public function closeLogDetailModal()
    {
        \Flux\Flux::modal('dashboard-log-detail-modal')->close();
        $this->selectedLog = null;
    }
};
?>

<div class="space-y-6">
    {{-- Banner Selamat Datang Purple Gradient --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary via-[#7a2478] to-purple-dark p-6 md:p-8 text-white shadow-lg">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <flux:icon name="calendar-days" class="w-64 h-64 text-white" />
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-xs font-semibold text-white/90 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Tahun Ajaran {{ $periode?->tahun_ajaran ?? 'Aktif' }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Selamat Datang di Sistem Jadwal Azmania</h1>
                <p class="text-xs md:text-sm text-purple-100 opacity-90 leading-relaxed">
                    Kelola dan pantau jadwal pelajaran SMP & MA secara real-time, seimbang, dan bebas bentrok dengan tampilan matriks interaktif.
                </p>
            </div>
            <div class="shrink-0 flex items-center gap-3">
                @if($periode)
                    <a href="{{ route('jadwal.index', $periode->id) }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-secondary hover:bg-amber-400 text-gray-900 font-extrabold text-xs md:text-sm shadow-md transition transform hover:-translate-y-0.5">
                        <flux:icon name="squares-2x2" class="w-4 h-4 text-gray-900" />
                        <span>Kelola Matriks Jadwal</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Kartu Ringkasan Statistik Utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs flex items-center justify-between hover:shadow-md transition">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mata Pelajaran</span>
                <div class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalMataPelajaran }}</div>
                <span class="text-[11px] text-primary dark:text-purple-300 font-semibold">Total Mapel Terdaftar</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-purple-300 shrink-0">
                <flux:icon name="book-open" class="w-6 h-6" />
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs flex items-center justify-between hover:shadow-md transition">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas Aktif</span>
                <div class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalKelas }}</div>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">Tingkat SMP & MA</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <flux:icon name="building-library" class="w-6 h-6" />
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs flex items-center justify-between hover:shadow-md transition">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Guru Pengajar</span>
                <div class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalGuru }}</div>
                <span class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold">Tenaga Pendidik</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <flux:icon name="academic-cap" class="w-6 h-6" />
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs flex items-center justify-between hover:shadow-md transition">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Slot Terisi</span>
                <div class="text-2xl md:text-3xl font-extrabold text-primary dark:text-purple-300">{{ $totalJadwal }}</div>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-semibold">Jam Pelajaran Terjadwal</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-dark/10 dark:bg-purple-dark/20 flex items-center justify-center text-purple-dark dark:text-purple-300 shrink-0">
                <flux:icon name="clock" class="w-6 h-6" />
            </div>
        </div>
    </div>

    {{-- Grid 2 Kolom Utama --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Kolom Kiri (7 Cols): Widget Jadwal Pelajaran Hari Ini --}}
        <div class="lg:col-span-7 space-y-4 bg-white dark:bg-gray-900 p-5 md:p-6 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-primary/10 text-primary dark:text-purple-300">
                        <flux:icon name="calendar-days" class="w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="font-bold text-base md:text-lg text-gray-900 dark:text-white">Jadwal Pelajaran Hari Ini</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ringkasan jadwal KBM per jam pelajaran</p>
                    </div>
                </div>

                {{-- Filter Chips Hari --}}
                <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0">
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                        <button type="button" wire:click="selectHari('{{ $hari }}')"
                            class="px-2.5 py-1 rounded-lg text-xs font-extrabold transition cursor-pointer shrink-0 {{ $selectedHari === $hari ? 'bg-primary text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            {{ $hari }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Timeline Jam Pelajaran --}}
            <div class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
                @forelse($this->todaySchedule as $scheduleRow)
                    @php
                        $jam = $scheduleRow['jam'];
                        $items = $scheduleRow['items'];
                        $isLive = $scheduleRow['is_live'];
                    @endphp
                    <div class="p-3.5 rounded-xl border transition {{ $isLive ? 'bg-purple-50/70 dark:bg-purple-950/40 border-primary ring-1 ring-primary/40' : 'bg-gray-50/50 dark:bg-gray-800/40 border-gray-200/80 dark:border-gray-800' }}">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs md:text-sm text-gray-900 dark:text-white">Jam {{ $jam->urutan }}</span>
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})</span>
                            </div>
                            @if($isLive)
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-primary text-white shadow-xs animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>BERLANGSUNG SAAT INI</span>
                                </span>
                            @endif
                        </div>

                        @if($items->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($items as $item)
                                    @php
                                        $bg = $item->guru->warna ?? '#902C8E';
                                        $textColor = \App\Helpers\ColorHelper::getTextColor($bg);
                                    @endphp
                                    <div class="p-2.5 rounded-lg text-xs font-semibold border border-black/5 shadow-2xs flex flex-col justify-center"
                                         style="background-color: {{ $bg }}; color: {{ $textColor }}">
                                        <div class="font-bold text-xs line-clamp-1">{{ $item->kelas?->nama_kelas ?? 'Semua Kelas' }}</div>
                                        <div class="text-[11px] opacity-90 font-medium line-clamp-1 mt-0.5">{{ $item->mataPelajaran?->nama_mapel ?? '-' }}</div>
                                        <div class="text-[10px] opacity-80 mt-0.5 line-clamp-1">{{ $item->guru?->nama_guru ?? 'Tanpa Guru' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs text-gray-400 italic">Tidak ada jadwal KBM pada jam ini</div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-gray-400 italic">Belum ada data jam pelajaran</div>
                @endforelse
            </div>
        </div>

        {{-- Kolom Kanan (5 Cols): Beban Mengajar Guru & Aktivitas Terbaru --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Widget Distribusi Jam Mengajar Guru --}}
            <div class="bg-white dark:bg-gray-900 p-5 md:p-6 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-lg bg-primary/10 text-primary dark:text-purple-300">
                            <flux:icon name="chart-bar" class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="font-bold text-base text-gray-900 dark:text-white">Beban Mengajar Guru</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Guru dengan alokasi JP terbanyak</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($this->guruLoadList as $guruLoad)
                        @php
                            $maxJp = $this->guruLoadList->first()?->total_jp ?? 1;
                            $percent = min(100, round(($guruLoad->total_jp / max(1, $maxJp)) * 100));
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-gray-800 dark:text-gray-200 line-clamp-1">{{ $guruLoad->guru?->nama_guru ?? 'Guru' }}</span>
                                <span class="font-extrabold text-primary dark:text-purple-300 shrink-0">{{ $guruLoad->total_jp }} JP</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400 italic text-center py-4">Belum ada alokasi jadwal guru</div>
                    @endforelse
                </div>
            </div>

            {{-- Widget Log Aktivitas Terbaru --}}
            <div class="bg-white dark:bg-gray-900 p-5 md:p-6 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-lg bg-primary/10 text-primary dark:text-purple-300">
                            <flux:icon name="clock" class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="font-bold text-base text-gray-900 dark:text-white">Aktivitas Terbaru</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Riwayat perubahan jadwal</p>
                        </div>
                    </div>
                    <a href="{{ route('log-aktivitas') }}" class="text-xs font-bold text-primary hover:underline">Lihat Semua</a>
                </div>

                <div class="space-y-3">
                    @forelse($this->recentLogs as $log)
                        <div wire:click="viewLogDetail({{ $log->id }})" 
                             class="flex items-start gap-3 text-xs p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 hover:bg-gray-100/80 dark:hover:bg-gray-800 transition cursor-pointer group">
                            <div class="w-2 h-2 rounded-full bg-primary shrink-0 mt-1.5 group-hover:scale-125 transition-transform"></div>
                            <div class="space-y-0.5 flex-1 min-w-0">
                                <div class="font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-primary transition-colors">
                                    {{ $log->short_description }}
                                </div>
                                <div class="text-[10px] text-gray-400 flex items-center justify-between gap-2">
                                    <span>{{ $log->user_name ?? ($log->user?->nama ?? 'Sistem') }}</span>
                                    <span>{{ $log->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                            <flux:icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 group-hover:text-primary transition-colors shrink-0 mt-1" />
                        </div>
                    @empty
                        <div class="text-xs text-gray-400 italic text-center py-4">Belum ada riwayat aktivitas</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Log Aktivitas Dashboard --}}
    <flux:modal name="dashboard-log-detail-modal" class="w-[90%] md:w-[680px]">
        @if($selectedLog)
            <div class="space-y-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        @if($selectedLog->action === 'create')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                <flux:icon name="plus-circle" class="w-3 h-3" />
                                <span>TAMBAH</span>
                            </span>
                        @elseif($selectedLog->action === 'update')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                <flux:icon name="pencil-square" class="w-3 h-3" />
                                <span>UBAH</span>
                            </span>
                        @elseif($selectedLog->action === 'delete')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-800">
                                <flux:icon name="trash" class="w-3 h-3" />
                                <span>HAPUS</span>
                            </span>
                        @endif
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                            {{ $selectedLog->module }}
                        </span>
                    </div>
                    <flux:heading size="lg">{{ $selectedLog->short_description }}</flux:heading>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 dark:bg-gray-800/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div>
                        <span class="font-semibold text-gray-500 dark:text-gray-400 block">Waktu:</span>
                        <span class="text-gray-900 dark:text-white font-bold">{{ $selectedLog->created_at?->translatedFormat('d F Y - H:i:s') }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-500 dark:text-gray-400 block">Pengubah:</span>
                        <span class="text-gray-900 dark:text-white font-bold">{{ $selectedLog->user_name }}</span>
                    </div>
                </div>

                @php
                    $properties = $selectedLog->properties ?? [];
                    $old = $properties['old'] ?? null;
                    $new = $properties['new'] ?? null;
                @endphp

                @if($selectedLog->action === 'update' && ($old || $new))
                    @php
                        $keys = array_unique(array_merge(array_keys($old ?? []), array_keys($new ?? [])));
                    @endphp
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                            <flux:icon name="pencil-square" class="w-4 h-4 text-blue-500" />
                            <span>Rincian Perubahan Nilai:</span>
                        </div>
                        <div class="grid grid-cols-1 gap-2.5 max-h-[350px] overflow-y-auto pr-1">
                            @foreach($keys as $key)
                                @php
                                    $label = $selectedLog->formatLabel($key);
                                    $oldVal = $selectedLog->formatValue($key, $old[$key] ?? null);
                                    $newVal = $selectedLog->formatValue($key, $new[$key] ?? null);
                                @endphp
                                <div class="bg-gray-50 dark:bg-gray-800/80 p-3 rounded-xl border border-gray-200 dark:border-gray-700 space-y-1.5">
                                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                                        <span>{{ $label }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="bg-red-50 dark:bg-red-950/40 p-2 rounded-lg border border-red-200 dark:border-red-900/50">
                                            <span class="text-[10px] font-bold text-red-600 dark:text-red-400 block mb-0.5 uppercase">Sebelum:</span>
                                            <span class="font-semibold text-red-900 dark:text-red-200 line-through decoration-red-400">{{ $oldVal }}</span>
                                        </div>
                                        <div class="bg-emerald-50 dark:bg-emerald-950/40 p-2 rounded-lg border border-emerald-200 dark:border-emerald-900/50">
                                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 block mb-0.5 uppercase">Sesudah:</span>
                                            <span class="font-bold text-emerald-900 dark:text-emerald-200">{{ $newVal }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($new)
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                            <flux:icon name="plus-circle" class="w-4 h-4 text-emerald-500" />
                            <span>Rincian Data Baru:</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[350px] overflow-y-auto p-1">
                            @foreach($new as $k => $v)
                                @php
                                    $label = $selectedLog->formatLabel($k);
                                    $val = $selectedLog->formatValue($k, $v);
                                @endphp
                                <div class="bg-emerald-50/60 dark:bg-emerald-950/30 p-2.5 rounded-xl border border-emerald-200 dark:border-emerald-900/40">
                                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 block mb-0.5">{{ $label }}</span>
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $val }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($old)
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-red-700 dark:text-red-400 uppercase tracking-wider flex items-center gap-1.5">
                            <flux:icon name="trash" class="w-4 h-4 text-red-500" />
                            <span>Rincian Data Terhapus:</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[350px] overflow-y-auto p-1">
                            @foreach($old as $k => $v)
                                @php
                                    $label = $selectedLog->formatLabel($k);
                                    $val = $selectedLog->formatValue($k, $v);
                                @endphp
                                @if(is_array($v))
                                    <div class="bg-red-50/60 dark:bg-red-950/30 p-2.5 rounded-xl border border-red-200 dark:border-red-900/40 col-span-full">
                                        <span class="text-[11px] font-semibold text-red-700 dark:text-red-400 block mb-1.5">{{ $label }} ({{ count($v) }})</span>
                                        <div class="space-y-1 max-h-48 overflow-y-auto text-xs text-red-900 dark:text-red-200 pr-1 divide-y divide-red-200/50 dark:divide-red-900/30">
                                            @foreach($v as $listItem)
                                                <div class="pt-1.5 first:pt-0 flex items-start gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 shrink-0 mt-1.5"></span>
                                                    <span>{{ $listItem }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-red-50/60 dark:bg-red-950/30 p-2.5 rounded-xl border border-red-200 dark:border-red-900/40">
                                        <span class="text-[11px] font-semibold text-red-700 dark:text-red-400 block mb-0.5">{{ $label }}</span>
                                        <span class="text-xs font-bold text-red-900 dark:text-red-200 line-through">{{ $val }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @elseif(!empty($properties))
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                            <flux:icon name="information-circle" class="w-4 h-4 text-primary" />
                            <span>Rincian Informasi:</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[350px] overflow-y-auto p-1">
                            @foreach($properties as $k => $v)
                                @php
                                    $label = $selectedLog->formatLabel($k);
                                    $val = $selectedLog->formatValue($k, $v);
                                @endphp
                                @if(is_array($v))
                                    <div class="bg-gray-50 dark:bg-gray-800/80 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 col-span-full">
                                        <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">{{ $label }} ({{ count($v) }})</span>
                                        <div class="space-y-1 max-h-48 overflow-y-auto text-xs text-gray-800 dark:text-gray-200 pr-1 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($v as $listItem)
                                                <div class="pt-1.5 first:pt-0 flex items-start gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0 mt-1.5"></span>
                                                    <span>{{ $listItem }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-gray-50 dark:bg-gray-800/80 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700">
                                        <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block mb-0.5">{{ $label }}</span>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $val }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($selectedLog->description !== $selectedLog->short_description)
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 p-2.5 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 leading-relaxed">
                        <span class="font-bold text-gray-600 dark:text-gray-300">Deskripsi Lengkap:</span> {{ $selectedLog->description }}
                    </div>
                @endif

                <div class="flex justify-end pt-2 border-t border-gray-200 dark:border-gray-700">
                    <flux:button wire:click="closeLogDetailModal" variant="subtle">Tutup</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>