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
    @endphp

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-zinc-200">
        <table class="w-full text-sm text-left text-zinc-700">
            <thead class="text-xs uppercase bg-zinc-100 text-zinc-600 border-b border-zinc-200">
                <tr>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">User / Admin</th>
                    <th class="px-4 py-3">Aksi</th>
                    <th class="px-4 py-3">Modul</th>
                    <th class="px-4 py-3">Deskripsi Aktivitas</th>
                    <th class="px-4 py-3 text-center">Aksi / Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white">
                @forelse($logs as $log)
                    <tr class="hover:bg-zinc-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-zinc-500">
                            {{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : '-' }}
                        </td>
                        <td class="px-4 py-3 font-medium text-zinc-900 whitespace-nowrap">
                            {{ $log->user_name ?? ($log->user?->nama ?? 'System') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($log->action === 'create')
                                <flux:badge color="green" size="sm">TAMBAH</flux:badge>
                            @elseif($log->action === 'update')
                                <flux:badge color="blue" size="sm">UBAH</flux:badge>
                            @elseif($log->action === 'delete')
                                <flux:badge color="red" size="sm">HAPUS</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ strtoupper($log->action) }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <flux:badge color="zinc" variant="outline" size="sm">{{ $log->module }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 font-medium text-zinc-800">
                            {{ $log->description }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <flux:button wire:click="viewDetail({{ $log->id }})" size="xs" variant="subtle" icon="eye">
                                Detail
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-zinc-400">
                            Belum ada riwayat aktivitas data yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $logs->links() }}
    </div>

    {{-- Modal Detail Perubahan Data --}}
    <flux:modal name="log-detail-modal" class="w-[90%] md:w-[680px]">
        @if($selectedLog)
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Detail Perubahan Data</flux:heading>
                    <flux:subheading>{{ $selectedLog->description }}</flux:subheading>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs bg-zinc-50 p-3 rounded-lg border border-zinc-200">
                    <div>
                        <span class="font-semibold text-zinc-500 block">Waktu:</span>
                        <span class="text-zinc-800 font-medium">{{ $selectedLog->created_at?->format('d F Y - H:i:s') }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-zinc-500 block">Pengubah:</span>
                        <span class="text-zinc-800 font-medium">{{ $selectedLog->user_name }}</span>
                    </div>
                </div>

                @php
                    $properties = $selectedLog->properties ?? [];
                    $old = $properties['old'] ?? null;
                    $new = $properties['new'] ?? null;
                @endphp

                @if($selectedLog->action === 'update' && ($old || $new))
                    <div class="overflow-x-auto rounded-lg border border-zinc-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-zinc-100 text-zinc-600 font-semibold uppercase">
                                <tr>
                                    <th class="px-3 py-2 border-b">Kolom</th>
                                    <th class="px-3 py-2 border-b bg-red-50 text-red-700">Nilai Sebelum (Old)</th>
                                    <th class="px-3 py-2 border-b bg-green-50 text-green-700">Nilai Sesudah (New)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white">
                                @php
                                    $keys = array_unique(array_merge(array_keys($old ?? []), array_keys($new ?? [])));
                                @endphp
                                @foreach($keys as $key)
                                    <tr>
                                        <td class="px-3 py-2 font-mono font-semibold text-zinc-700 bg-zinc-50">{{ $key }}</td>
                                        <td class="px-3 py-2 text-red-600 bg-red-50/30 break-all font-mono">
                                            {{ is_array($old[$key] ?? null) ? json_encode($old[$key]) : ($old[$key] ?? '-') }}
                                        </td>
                                        <td class="px-3 py-2 text-green-600 bg-green-50/30 break-all font-mono">
                                            {{ is_array($new[$key] ?? null) ? json_encode($new[$key]) : ($new[$key] ?? '-') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif($new)
                    <div>
                        <h4 class="text-xs font-semibold text-zinc-600 mb-2 uppercase">Data Baru Ditambahkan</h4>
                        <div class="overflow-x-auto rounded-lg border border-zinc-200">
                            <table class="w-full text-xs">
                                <thead class="bg-zinc-100 text-zinc-600 font-semibold uppercase">
                                    <tr>
                                        <th class="px-3 py-2">Field</th>
                                        <th class="px-3 py-2">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                    @foreach($new as $k => $v)
                                        <tr>
                                            <td class="px-3 py-2 font-mono font-semibold text-zinc-700 bg-zinc-50 w-1/3">{{ $k }}</td>
                                            <td class="px-3 py-2 font-mono text-zinc-800">{{ is_array($v) ? json_encode($v) : $v }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($old)
                    <div>
                        <h4 class="text-xs font-semibold text-red-600 mb-2 uppercase">Data Dihapus</h4>
                        <div class="overflow-x-auto rounded-lg border border-zinc-200">
                            <table class="w-full text-xs">
                                <thead class="bg-zinc-100 text-zinc-600 font-semibold uppercase">
                                    <tr>
                                        <th class="px-3 py-2">Field</th>
                                        <th class="px-3 py-2">Nilai Terhapus</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                    @foreach($old as $k => $v)
                                        <tr>
                                            <td class="px-3 py-2 font-mono font-semibold text-zinc-700 bg-zinc-50 w-1/3">{{ $k }}</td>
                                            <td class="px-3 py-2 font-mono text-red-600">{{ is_array($v) ? json_encode($v) : $v }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end pt-2">
                    <flux:button wire:click="closeDetailModal" variant="subtle">Tutup</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
