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
                        <div class="flex items-start gap-3 text-xs p-2.5 rounded-xl bg-gray-50/70 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <div class="w-2 h-2 rounded-full bg-primary shrink-0 mt-1.5"></div>
                            <div class="space-y-0.5 flex-1 min-w-0">
                                <div class="font-bold text-gray-900 dark:text-white line-clamp-1">{{ $log->description }}</div>
                                <div class="text-[10px] text-gray-400 flex items-center justify-between gap-2">
                                    <span>{{ $log->user?->name ?? 'Sistem' }}</span>
                                    <span>{{ $log->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400 italic text-center py-4">Belum ada riwayat aktivitas</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>