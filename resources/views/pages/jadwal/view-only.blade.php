<?php

use App\Helpers\JadwalHelper;
use App\Models\Periode;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Lihat Jadwal Pelajaran')] class extends Component {
    public $periode_id;
    public $tahunAjaran;
    public $periodeList = [];

    public function mount()
    {
        $this->periodeList = Periode::orderBy('tahun_ajaran', 'desc')->get();

        if (request()->has('periode_id')) {
            $this->periode_id = request()->get('periode_id');
        } else {
            $firstPeriode = JadwalHelper::getFirstPeriode();
            $this->periode_id = $firstPeriode?->id;
        }

        $this->tahunAjaran = JadwalHelper::getTahunAjaran($this->periode_id);
    }

    public function updatedPeriodeId($value)
    {
        $this->tahunAjaran = JadwalHelper::getTahunAjaran($value);
    }
};
?>

<div class="space-y-6">
    <div class="dash-card">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <flux:icon name="book-open" class="w-6 h-6 text-primary" />
                    <span>Jadwal Pelajaran Azmania</span>
                </flux:heading>
                <flux:subheading size="sm" class="mt-1">
                    Tampilan jadwal pelajaran aktif (Mode Baca & Cetak)
                </flux:subheading>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Periode Selector --}}
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Periode:</label>
                    <select wire:model.live="periode_id"
                        class="text-xs px-3 py-1.5 rounded-lg bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 font-semibold shadow-xs focus:ring-2 focus:ring-primary">
                        @foreach ($periodeList as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->tahun_ajaran }} ({{ $p->semester }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons (PDF & Excel) --}}
                @if ($periode_id)
                    <a href="{{ route('export-jadwal.pdf', ['periode_id' => $periode_id]) }}" target="_blank">
                        <flux:button icon="printer" variant="outline" size="sm" class="!text-red-600 border-red-200 hover:bg-red-50">
                            Cetak PDF
                        </flux:button>
                    </a>
                    <a href="{{ route('export-jadwal.excel', ['periode_id' => $periode_id]) }}" target="_blank">
                        <flux:button icon="arrow-down-tray" variant="outline" size="sm" class="!text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                            Export Excel
                        </flux:button>
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if ($periode_id)
        <livewire:datatable.jadwal-matrix :periode_id="$periode_id" />
    @else
        <div class="dash-card text-center py-12 text-gray-500 dark:text-gray-400">
            <flux:icon name="exclamation-circle" class="w-10 h-10 mx-auto text-amber-500 mb-2" />
            <p class="font-semibold">Belum ada data periode jadwal yang dibuat.</p>
        </div>
    @endif
</div>
