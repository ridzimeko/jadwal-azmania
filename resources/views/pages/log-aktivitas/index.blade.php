<?php

use App\Models\ActivityLog;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Title('Log Aktivitas')]
#[Layout('layouts.app')]
class extends Component {
    use WithPagination;

    public string $search = '';
    public string $actionFilter = '';
    public string $moduleFilter = '';

    public ?ActivityLog $selectedLog = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedActionFilter()
    {
        $this->resetPage();
    }

    public function updatedModuleFilter()
    {
        $this->resetPage();
    }

    public function viewDetail(int $logId)
    {
        $this->selectedLog = ActivityLog::find($logId);
        if ($this->selectedLog) {
            Flux::modal('log-detail-modal')->show();
        }
    }

    public function closeDetailModal()
    {
        Flux::modal('log-detail-modal')->close();
        $this->selectedLog = null;
    }

    public function formatAttrLabel(string $key): string
    {
        $labels = [
            'kelas_id' => 'Kelas',
            'guru_id' => 'Guru Pengajar',
            'mata_pelajaran_id' => 'Mata Pelajaran',
            'jam_pelajaran_id' => 'Jam Pelajaran',
            'periode_id' => 'Tahun Ajaran / Periode',
            'hari' => 'Hari',
            'urutan' => 'Urutan Jam',
            'jam_mulai' => 'Jam Mulai',
            'jam_selesai' => 'Jam Selesai',
            'nama' => 'Nama',
            'nama_guru' => 'Nama Guru',
            'nama_kelas' => 'Nama Kelas',
            'nama_mapel' => 'Nama Mata Pelajaran',
            'kode_kelas' => 'Kode Kelas',
            'tingkat' => 'Tingkat',
            'tahun_ajaran' => 'Tahun Ajaran',
            'username' => 'Username',
            'email' => 'Email',
            'role' => 'Role Access',
            'is_active' => 'Status Aktif',
            'warna' => 'Warna Identitas',
            'Durasi Jam' => 'Durasi Jam (JP)',
            'Kelas' => 'Kelas',
            'Mata Pelajaran' => 'Mata Pelajaran',
            'Guru Pengajar' => 'Guru Pengajar',
            'Hari' => 'Hari',
        ];
        return $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    public function formatAttrValue(string $key, mixed $value): string
    {
        if (is_null($value) || $value === '') return '-';

        if ($key === 'kelas_id') {
            return \App\Models\Kelas::find($value)?->nama_kelas ?? "Kelas #{$value}";
        }
        if ($key === 'guru_id') {
            return \App\Models\Guru::find($value)?->nama_guru ?? "Guru #{$value}";
        }
        if ($key === 'mata_pelajaran_id') {
            return \App\Models\MataPelajaran::find($value)?->nama_mapel ?? "Mapel #{$value}";
        }
        if ($key === 'jam_pelajaran_id') {
            $j = \App\Models\JamPelajaran::find($value);
            return $j ? (is_numeric($j->urutan) ? "Jam {$j->urutan} ({$j->jam_mulai} - {$j->jam_selesai})" : "{$j->urutan} ({$j->jam_mulai} - {$j->jam_selesai})") : "Jam #{$value}";
        }
        if ($key === 'periode_id') {
            return \App\Models\Periode::find($value)?->tahun_ajaran ?? "Periode #{$value}";
        }
        if ($key === 'is_active') {
            return $value ? 'Aktif' : 'Non-Aktif';
        }

        if (is_array($value)) return implode(', ', $value);

        return (string) $value;
    }
};
?>

<div class="dash-card space-y-6">
    <x-card-heading title="Log Aktivitas Pengguna" description="Riwayat riil perubahan data (tambah, ubah, hapus) di seluruh aplikasi.">
    </x-card-heading>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari deskripsi, user, modul..." icon="magnifying-glass" />

        <flux:select wire:model.live="actionFilter" placeholder="Filter Semua Aksi">
            <flux:select.option value="">Semua Aksi</flux:select.option>
            <flux:select.option value="create">Tambah (Create)</flux:select.option>
            <flux:select.option value="update">Ubah (Update)</flux:select.option>
            <flux:select.option value="delete">Hapus (Delete)</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="moduleFilter" placeholder="Filter Semua Modul">
            <flux:select.option value="">Semua Modul</flux:select.option>
            <flux:select.option value="Jadwal Pelajaran">Jadwal Pelajaran</flux:select.option>
            <flux:select.option value="Admin / User">Admin / User</flux:select.option>
            <flux:select.option value="Mata Pelajaran">Mata Pelajaran</flux:select.option>
            <flux:select.option value="Guru">Guru</flux:select.option>
            <flux:select.option value="Kelas">Kelas</flux:select.option>
            <flux:select.option value="Jam Pelajaran">Jam Pelajaran</flux:select.option>
            <flux:select.option value="Kegiatan">Kegiatan</flux:select.option>
            <flux:select.option value="Periode Jadwal">Periode Jadwal</flux:select.option>
        </flux:select>
    </div>

    @php
        $logs = ActivityLog::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%");
                });
            })
            ->when($actionFilter, fn ($q) => $q->where('action', $actionFilter))
            ->when($moduleFilter, fn ($q) => $q->where('module', $moduleFilter))
            ->latest()
            ->paginate(15);

        $groupedLogs = $logs->getCollection()->groupBy(function($item) {
            return $item->created_at ? $item->created_at->format('Y-m-d') : 'others';
        });
    @endphp

    {{-- Activity Feed Grouped By Day --}}
    @if($logs->isEmpty())
        <div class="p-8 text-center text-gray-400 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
            <flux:icon name="clock" class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
            <p class="font-medium text-sm">Belum ada riwayat aktivitas data yang tercatat.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($groupedLogs as $dateKey => $dayLogs)
                @php
                    if ($dateKey === 'others') {
                        $dateLabel = 'Lainnya';
                    } else {
                        $carbonDate = \Carbon\Carbon::parse($dateKey);
                        if ($carbonDate->isToday()) {
                            $dateLabel = 'Hari Ini — ' . $carbonDate->translatedFormat('l, d F Y');
                        } elseif ($carbonDate->isYesterday()) {
                            $dateLabel = 'Kemarin — ' . $carbonDate->translatedFormat('l, d F Y');
                        } else {
                            $dateLabel = $carbonDate->translatedFormat('l, d F Y');
                        }
                    }
                @endphp

                <div class="space-y-3">
                    {{-- Date Group Banner Header --}}
                    <div class="flex items-center gap-3 sticky top-0 z-10 py-1.5 bg-gray-50/90 dark:bg-gray-900/90 backdrop-blur-sm">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20 shadow-2xs">
                            <flux:icon name="calendar-days" class="w-3.5 h-3.5 text-primary" />
                            <span>{{ $dateLabel }}</span>
                        </span>
                        <div class="h-px bg-gray-200 dark:border-gray-800 flex-1"></div>
                        <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 px-2 py-0.5 rounded-md border border-gray-200 dark:border-gray-700">
                            {{ count($dayLogs) }} Aktivitas
                        </span>
                    </div>

                    {{-- Activity Table for the Day --}}
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs bg-white dark:bg-gray-900">
                        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
                            <thead class="text-xs uppercase bg-gray-50/80 dark:bg-gray-800/80 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 font-bold">
                                <tr>
                                    <th class="px-4 py-2.5 w-28">Jam</th>
                                    <th class="px-4 py-2.5 w-44">Pengubah</th>
                                    <th class="px-4 py-2.5 w-28">Aksi</th>
                                    <th class="px-4 py-2.5 w-40">Modul</th>
                                    <th class="px-4 py-2.5">Deskripsi Aktivitas</th>
                                    <th class="px-4 py-2.5 text-center w-24">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($dayLogs as $log)
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }}</div>
                                            <div class="text-[10px] text-gray-400">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</div>
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-primary/10 text-primary font-bold text-[11px] flex items-center justify-center border border-primary/20 shrink-0">
                                                    {{ strtoupper(substr($log->user_name ?? ($log->user?->nama ?? 'S'), 0, 1)) }}
                                                </div>
                                                <span>{{ $log->user_name ?? ($log->user?->nama ?? 'System') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($log->action === 'create')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                    <flux:icon name="plus-circle" class="w-3.5 h-3.5" />
                                                    <span>TAMBAH</span>
                                                </span>
                                            @elseif($log->action === 'update')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                                    <flux:icon name="pencil-square" class="w-3.5 h-3.5" />
                                                    <span>UBAH</span>
                                                </span>
                                            @elseif($log->action === 'delete')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-800">
                                                    <flux:icon name="trash" class="w-3.5 h-3.5" />
                                                    <span>HAPUS</span>
                                                </span>
                                            @else
                                                <flux:badge color="zinc" size="sm">{{ strtoupper($log->action) }}</flux:badge>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                                {{ $log->module }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                                            {{ $log->description }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <flux:button wire:click="viewDetail({{ $log->id }})" size="xs" variant="subtle" icon="eye">
                                                Detail
                                            </flux:button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $logs->links() }}
    </div>

    {{-- Modal Detail Perubahan Data (User Friendly UI) --}}
    <flux:modal name="log-detail-modal" class="w-[90%] md:w-[680px]">
        @if($selectedLog)
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Detail Perubahan Data</flux:heading>
                    <flux:subheading class="text-gray-600 dark:text-gray-300 font-medium mt-0.5">{{ $selectedLog->description }}</flux:subheading>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 dark:bg-gray-800/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div>
                        <span class="font-semibold text-gray-500 dark:text-gray-400 block">Waktu:</span>
                        <span class="text-gray-900 dark:text-white font-bold">{{ $selectedLog->created_at?->format('d F Y - H:i:s') }}</span>
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
                                    $label = $this->formatAttrLabel($key);
                                    $oldVal = $this->formatAttrValue($key, $old[$key] ?? null);
                                    $newVal = $this->formatAttrValue($key, $new[$key] ?? null);
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
                            <span>Rincian Data Baru Ditambahkan:</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[350px] overflow-y-auto p-1">
                            @foreach($new as $k => $v)
                                @php
                                    $label = $this->formatAttrLabel($k);
                                    $val = $this->formatAttrValue($k, $v);
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
                                    $label = $this->formatAttrLabel($k);
                                    $val = $this->formatAttrValue($k, $v);
                                @endphp
                                <div class="bg-red-50/60 dark:bg-red-950/30 p-2.5 rounded-xl border border-red-200 dark:border-red-900/40">
                                    <span class="text-[11px] font-semibold text-red-700 dark:text-red-400 block mb-0.5">{{ $label }}</span>
                                    <span class="text-xs font-bold text-red-900 dark:text-red-200 line-through">{{ $val }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex justify-end pt-2 border-t border-gray-200 dark:border-gray-700">
                    <flux:button wire:click="closeDetailModal" variant="subtle">Tutup</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
