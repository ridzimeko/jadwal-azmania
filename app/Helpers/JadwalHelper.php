<?php

namespace App\Helpers;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\Cache;

class JadwalHelper
{
    /**
     * Cek apakah jadwal baru tersedia (tidak bentrok)
     * dan kembalikan detail bentrok jika ada.
     *
     * @param  array  $data  ['hari', 'jam_mulai', 'jam_selesai', 'guru_id', 'kelas_id']
     * @param  int|null  $ignoreId  abaikan ID tertentu saat edit data
     * @return array
     */
    public static function isAvailable(array $data, ?int $ignoreId = null): array
    {
        $query = JadwalPelajaran::query()
            ->where('hari', $data['hari'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('jam_mulai', [$data['jam_mulai'], $data['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$data['jam_mulai'], $data['jam_selesai']])
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('jam_mulai', '<', $data['jam_mulai'])
                            ->where('jam_selesai', '>', $data['jam_selesai']);
                    });
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        // // 🔸 Cek bentrok berdasarkan guru dan kelas
        // $query->where(function ($q) use ($data) {
        //     $q->where('guru_id', $data['guru_id'])
        //       ->orWhere('kelas_id', $data['kelas_id']);
        // });

        $bentrok = $query->with(['guru', 'kelas', 'mataPelajaran'])->get();

        if ($bentrok->isNotEmpty()) {
            return [
                'available' => false,
                'bentrok' => $bentrok->map(function ($item) {
                    return [
                        'hari' => $item->hari,
                        'jam_mulai' => $item->jam_mulai,
                        'jam_selesai' => $item->jam_selesai,
                        'guru' => $item->guru->nama_guru ?? '-',
                        'kelas' => $item->kelas->nama_kelas ?? '-',
                        'mapel' => $item->mataPelajaran->nama_mapel ?? '-',
                    ];
                }),
            ];
        }

        return ['available' => true, 'bentrok' => collect()];
    }

    public static function getQuery($tingkat)
    {
        $query = JadwalPelajaran::query()
            ->with(['kelas', 'mataPelajaran', 'guru'])
            ->withBentrok()
            ->orderByDesc('is_bentrok')
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai');

        if ($tingkat) {
            $query->whereRelation('kelas', 'tingkat', $tingkat);
        }

        return $query;
    }

    public static function getKelasOptions(string $tingkat)
    {
        if ($tingkat) {
            return Cache::remember("kelas_options_{$tingkat}", 60 * 60, function () use ($tingkat) {
                return Kelas::where('tingkat', $tingkat)
                    ->orderBy('nama_kelas')
                    ->get()
                    ->map(fn($g) => [
                        'value' => $g->id,
                        'label' => $g->nama_kelas,
                    ])
                    ->toArray();
            });
        }

        return Cache::remember("kelas_options", 60 * 60, function () {
            return Kelas::orderBy('nama_kelas')
                ->get()
                ->map(fn($g) => [
                    'value' => $g->id,
                    'label' => $g->nama_kelas,
                ])
                ->toArray();
        });
    }

    public static function getMapelOptions()
    {
        return Cache::remember("mapel_options", 60 * 60, function () {
            return MataPelajaran::orderBy('nama_mapel')
                ->get()
                ->map(fn($g) => ['value' => $g->id, 'label' => $g->nama_mapel])
                ->toArray();
        });
    }

    public static function getHariOptions()
    {
        return collect(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])
            ->map(fn($hari) => ['label' => $hari, 'value' => $hari])
            ->toArray();
    }

    public static function getGuruOptions()
    {
        return Cache::remember('guru_options', 60 * 60, function () {
            return Guru::orderBy('nama_guru')
                ->get()
                ->map(fn($g) => [
                    'value' => $g->id,
                    'label' => $g->nama_guru,
                ])
                ->toArray();
        });
    }
}
