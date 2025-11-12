<?php

use App\Helpers\JadwalHelper;
use App\Models\Kelas;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    public $periode_id;

    public string $tingkat = 'SMP'; // bisa di-pass lewat route

    public $hari;

    public $jadwal;

    public function placeholder()
    {
        return view('components.loading');
    }

    #[Computed]
    public function getKelas()
    {
        $kelasQuery = Kelas::query()->orderBy('nama_kelas');
        if ($this->tingkat) {
            $kelasQuery->where('tingkat', $this->tingkat);
        }
        return $kelasQuery
            ->whereNotIn('kode_kelas', ['SMP', 'MA'])
            ->get();
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
                return $item->jamPelajaran->jam_mulai . ' - ' . $item->jamPelajaran->jam_selesai;
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
            <thead class="bg-primary text-white">
                @if ($this->hari)
                    <tr>
                        <th colspan="{{ 2 + count($kelasList) }}"
                            class="bg-[#fee685] text-black px-4 py-2 border text-center">{{ $this->hari }}</th>
                    </tr>
                @endif
                <tr>
                    <th class="px-4 py-2 border text-center">No</th>

                    @if (!$this->hari)
                        <th class="px-4 py-2 border text-center">Hari</th>
                    @endif

                    <th class="px-4 py-2 border text-center w-[160px] sticky left-[-1px] bg-primary text-white">Jam</th>

                    @foreach ($kelasList as $kelas)
                        <th class="px-4 py-2 border text-center min-w-[120px]">{{ $kelas->nama_kelas }}</th>
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

                            <td class="px-4 py-2 border text-center sticky left-[-1px] bg-white">{{ $jamLabel }}
                            </td>

                            @php
                                $is_global = $items->filter(function ($item) {
                                    return in_array($item->kelas?->kode_kelas, ['SMP', 'MA']);
                                });
                            @endphp

                            @if ($is_global->count() > 0)
                                <td colspan="{{ 2 + count($kelasList) }}"
                                    class="px-4 py-2 border text-center align-top {{ $items->first()?->is_bentrok ? 'bg-red-100 text-red-700' : '' }}">
                                    {{-- Jika kolom ada data --}}
                                    @if ($items->count() > 0)
                                        @foreach ($items as $item)
                                            @php
                                                $bg = $item->guru->warna ?? '#ffffff';
                                                $text = \App\Helpers\ColorHelper::getTextColor($bg);
                                            @endphp
                                            <button
                                                class="w-full mb-2 p-2 rounded cursor-pointer hover:bg-yellow-100 transition"
                                                style="background-color: {{ $bg }}; color: {{ $text }}"
                                                wire:click="$parent.openEditJadwal({{ json_encode([
                                                    'id' => $item->id,
                                                    'hari' => $hariKey,
                                                    'kelas_id' => $item->kelas_id,
                                                    'mata_pelajaran_id' => $item->mata_pelajaran_id,
                                                    'jam_pelajaran_id' => $item->jam_pelajaran_id,
                                                    'guru_id' => $item->guru_id,
                                                ]) }} 
                                                );">
                                                <div class="font-semibold">{{ $item->mataPelajaran->nama_mapel }}</div>
                                                <div class="text-xs">{{ $item->guru->nama_guru ?? null }}</div>
                                            </button>
                                            @if (!$loop->last)
                                                <hr class="my-1 border-gray-200">
                                            @endif
                                        @endforeach
                                    @endif
                                </td>

                                {{-- Jadwal per kelas --}}
                            @else
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
                                                <button
                                                    class="mb-2 p-2 rounded cursor-pointer hover:bg-yellow-100 transition"
                                                    style="background-color: {{ $bg }}; color: {{ $text }}"
                                                    wire:click="$parent.openEditJadwal({{ json_encode([
                                                        'id' => $item->id,
                                                        'hari' => $hariKey,
                                                        'kelas_id' => $kelas->id,
                                                        'mata_pelajaran_id' => $item->mata_pelajaran_id,
                                                        'jam_pelajaran_id' => $item->jam_pelajaran_id,
                                                        'guru_id' => $item->guru_id,
                                                    ]) }} 
                                                    );">
                                                    <div class="font-semibold">{{ $item->mataPelajaran->nama_mapel }}</div>
                                                    <div class="text-xs">{{ $item->guru->nama_guru ?? null }}</div>
                                                </button>
                                                @if (!$loop->last)
                                                    <hr class="my-1 border-gray-200">
                                                @endif
                                            @endforeach

                                            {{-- Jika kolom kosong --}}
                                        @else
                                            <div class="text-gray-400 italic py-4 cursor-pointer hover:bg-green-50 hover:text-green-600 transition rounded"
                                                wire:click="$parent.openEditJadwal({{ json_encode([
                                                    'hari' => $hariKey,
                                                    'kelas_id' => $kelas->id,
                                                    'mata_pelajaran_id' => null,
                                                    'jam_pelajaran_id' => null,
                                                    'guru_id' => null,
                                                ]) }})">
                                                Tambah +
                                            </div>
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
        <div class="w-full rounded-md py-20 flex flex-col gap-4 items-center justify-center text-center">
            <flux:icon name="question-mark-circle" class="size-12 text-zinc-800/45" />
            <p class="font-semibold">Tidak ada data jadwal</p>
        </div>
    @endif
</div>
