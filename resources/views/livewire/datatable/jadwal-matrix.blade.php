<?php

use App\Helpers\JadwalHelper;
use App\Models\Kelas;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    #[Reactive]
    public $periode_id;

    #[Reactive]
    public string $tingkat = 'SMP'; // bisa di-pass lewat route

    #[Reactive]
    public $hari;

    public $jadwal;

    public function placeholder()
    {
        return <<<'HTML'
        <div class="grid place-items-center">
            <div
                class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-e-transparent align-[-0.125em] text-surface motion-reduce:animate-[spin_1.5s_linear_infinite] dark:text-white"
                role="status">
            <span
                class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]"
                >Loading...</span
            >
            </div>
        </div>
        HTML;
    }

    #[Computed]
    public function getKelas()
    {
        $kelasQuery = Kelas::query()->orderBy('nama_kelas');
        if ($this->tingkat) {
            $kelasQuery->where('tingkat', $this->tingkat);
        }
        return $kelasQuery->get();
    }

    #[Computed]
    public function getJadwal($id = null)
    {
        $query = JadwalHelper::getQuery($this->periode_id, $this->tingkat);

        if ($id) {
            return $query->where('id', $id)->get();
        }

        // Filter berdasarkan hari jika ada parameter
        if ($this->hari) {
            $query->where('hari', $this->hari);
        }

        $jadwal = $query->get()->groupBy([
            'hari',
            function ($item) {
                return $item->jam_mulai . ' - ' . $item->jam_selesai;
            },
        ]);

        // Grup berdasarkan hari dan jam_mulai + jam_selesai
        return $jadwal;
    }

    #[On('refreshJadwalTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }
};

?>

<div class="w-full overflow-auto">
    @php
        $jadwalList = $this->getJadwal();
        $kelasList = $this->getKelas();
    @endphp
    @if (count($jadwalList) >= 1)
        <table class="min-w-full border border-gray-300 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border text-center">No</th>

                    @if (!$this->hari)
                        <th class="px-4 py-2 border text-center">Hari</th>
                    @endif

                    <th class="px-4 py-2 border text-center w-[160px]">Jam</th>

                    @foreach ($kelasList as $kelas)
                        <th class="px-4 py-2 border text-center w-[160px]">{{ $kelas->nama_kelas }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($jadwalList as $hariKey => $jadwalHari)
                    @foreach ($jadwalHari as $jamLabel => $items)
                        <tr>
                            <td class="px-4 py-2 border text-center">{{ $no++ }}</td>

                            @if (!$this->hari)
                                <td class="px-4 py-2 border text-center">{{ $hariKey }}</td>
                            @endif

                            <td class="px-4 py-2 border text-center">{{ $jamLabel }}</td>

                            @foreach ($kelasList as $kelas)
                                @php
                                    $kelasItems = $items->where('kelas_id', $kelas->id);
                                    $jam_mapel = array_map('trim', explode('-', $jamLabel));
                                @endphp

                                <td
                                    class="px-4 py-2 border text-center align-top {{ $kelasItems->first()?->is_bentrok ? 'bg-red-100 text-red-700' : '' }}">
                                    {{-- Jika kolom ada data --}}
                                    @if ($kelasItems->count() > 0)
                                        @foreach ($kelasItems as $item)
                                            @php
                                                $bg = $item->guru->warna ?? '#ffffff';
                                                $text = \App\Helpers\ColorHelper::getTextColor($bg);
                                            @endphp
                                            <div class="mb-2 p-2 rounded cursor-pointer hover:bg-yellow-100 transition"
                                                style="background-color: {{ $bg }}; color: {{ $text }}"
                                                                                                wire:click="$dispatch('openEditJadwal', { record: @js([
                                                    'id' => $item->id,
                                                    'hari' => $item->hari,
                                                    'jam_mulai' => $item->jam_mulai,
                                                    'jam_selesai' => $item->jam_selesai,
                                                    'kelas_id' => $item->kelas_id,
                                                    'mata_pelajaran_id' => $item->mata_pelajaran_id,
                                                    'guru_id' => $item->guru_id,
                                                ]) })">
                                                <div class="font-semibold">{{ $item->mataPelajaran->nama_mapel }}</div>
                                                <div class="text-xs">{{ $item->guru->nama_guru }}</div>
                                            </div>
                                            @if (!$loop->last)
                                                <hr class="my-1 border-gray-200">
                                            @endif
                                        @endforeach

                                        {{-- Jika kolom kosong --}}
                                    @else
                                        <div class="text-gray-400 italic py-4 cursor-pointer hover:bg-green-50 hover:text-green-600 transition rounded"
                                            wire:click="$dispatch('openEditJadwal', { record: @js([
                                                'hari' => $hariKey,
                                                'jam_mulai' => $jam_mapel[0] ?? null,
                                                'jam_selesai' => $jam_mapel[1] ?? null,
                                                'kelas_id' => $kelas->id,
                                                'mata_pelajaran_id' => null,
                                                'guru_id' => null,
                                            ]) })">
                                            Tambah +
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @else
        <div class="w-full rounded-md py-20 flex flex-col gap-4 items-center justify-center text-center">
            <flux:icon name="question-mark-circle" class="size-12 text-zinc-800/45" />
            <p class="font-semibold">Tidak ada data jadwal</p>
        </div>
    @endif
</div>
