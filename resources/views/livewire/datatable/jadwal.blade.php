<?php

use App\Models\JadwalPelajaran; // Pastikan model JadwalPelajaran di-import
use App\Models\Kelas;
use Filament\Actions\Action;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public string $tingkat = 'SMP'; // bisa di-pass lewat route
    public $kelasList;
    public $jadwal;
    protected $hari;

    #[Computed]
    public function getJadwal($id = null) {
        $query = JadwalPelajaran::with(['guru', 'mataPelajaran', 'kelas'])
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai');

        if ($id) {
            return $query->where('id', $id)->get();
        }

        // Filter berdasarkan hari jika ada parameter
        if ($this->hari) {
            $query->where('hari', $this->hari);
        }

        $jadwal = $query->get();

        // Grup berdasarkan hari dan jam_mulai + jam_selesai
        return $jadwal->groupBy(['hari', function ($item) {
            return $item->jam_mulai . ' - ' . $item->jam_selesai;
        }]);
    }

    #[Computed]
    public function getKelas() {
        return Kelas::where('tingkat', $this->tingkat)->orderBy('nama_kelas')->get();
    }

    #[On('refreshJadwalTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->color('danger')
            ->requiresConfirmation();
            // ->action(fn () => $this->getJadwal($id)->delete());
    }
}

?>

<div class="overflow-x-auto">
<table class="min-w-full border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 border text-center">No</th>

                @if(!$this->hari)
                    <th class="px-4 py-2 border text-center">Hari</th>
                @endif

                <th class="px-4 py-2 border text-center">Jam</th>

                @foreach($this->getKelas() as $kelas)
                    <th class="px-4 py-2 border text-center">{{ $kelas->nama_kelas }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($this->getJadwal() as $hariKey => $jadwalHari)
                @foreach($jadwalHari as $jamLabel => $items)
                    <tr>
                        <td class="px-4 py-2 border text-center">{{ $no++ }}</td>

                        @if(!$this->hari)
                            <td class="px-4 py-2 border text-center">{{ $hariKey }}</td>
                        @endif

                        <td class="px-4 py-2 border text-center">{{ $jamLabel }}</td>

                        @foreach($this->getKelas() as $kelas)
                            @php
                                $item = $items->firstWhere('kelas_id', $kelas->id);
                                $jam_mapel = array_map('trim', explode('-', $jamLabel));
                            @endphp
                            <td class="px-4 py-2 border text-center cursor-pointer hover:bg-yellow-100 transition"
                                wire:click="$dispatch('openEditJadwal', { record: @js($item ?? [
                                    'hari' => $hariKey,
                                    'jam_mulai' => $jam_mapel[0] ?? null,
                                    'jam_selesai' => $jam_mapel[1] ?? null,
                                    'kelas_id' => $kelas->id,
                                    'mata_pelajaran_id' => null,
                                    'guru_id' => null
                                ])})">
                                @if($item)
                                    <div class="font-semibold">{{ $item->mataPelajaran->nama_mapel }}</div>
                                    <div class="text-xs text-gray-600">{{ $item->guru->nama_guru }}</div>
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
