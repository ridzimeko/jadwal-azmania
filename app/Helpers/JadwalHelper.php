<?php

namespace App\Helpers;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Periode;
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
        // Resolve jam_mulai / jam_selesai either from payload or from JamPelajaran model (if jam_pelajaran_id provided)
        $jamMulai = $data['jam_mulai'] ?? null;
        $jamSelesai = $data['jam_selesai'] ?? null;

        if (isset($data['jam_pelajaran_id']) && (!$jamMulai || !$jamSelesai)) {
            $jp = \App\Models\JamPelajaran::find($data['jam_pelajaran_id']);
            if ($jp) {
                $jamMulai = $jp->jam_mulai;
                $jamSelesai = $jp->jam_selesai;
            }
        }

        // if we still don't have times, treat as available (no sensible overlap check)
        if (!$jamMulai || !$jamSelesai) {
            return ['available' => true, 'bentrok' => collect()];
        }

        $query = JadwalPelajaran::query()
            ->with(['guru', 'kelas', 'mataPelajaran', 'jamPelajaran'])
            ->where('hari', $data['hari'])
            ->whereHas('jamPelajaran', function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                    ->where('jam_selesai', '>', $jamMulai);
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        // Cek data kelas (aman walau null)
        $kelas = isset($data['kelas_id']) ? Kelas::find($data['kelas_id']) : null;
        $kodeKelas = $kelas->kode_kelas ?? null;

        // Kalau bukan SMP/MA, tetap cek bentrok guru dan kelas
        if (!in_array($kodeKelas, ['SMP', 'MA'])) {
            $query->where(function ($q) use ($data) {
                $q->where('guru_id', $data['guru_id'])
                    ->orWhere('kelas_id', $data['kelas_id']);
            });
        }

        if (in_array($kodeKelas, ['SMP', 'MA'])) {
            $query->whereRelation('kelas', 'tingkat', $kodeKelas);
        }

        $bentrok = $query->get();

        if ($bentrok->isNotEmpty()) {
            return [
                'available' => false,
                'bentrok' => $bentrok->map(function ($item) {
                    return [
                        'hari' => $item->hari,
                        'jam_mulai' => $item->jamPelajaran->jam_mulai ?? $item->jam_mulai ?? '-',
                        'jam_selesai' => $item->jamPelajaran->jam_selesai ?? $item->jam_selesai ?? '-',
                        'guru' => $item->guru->nama_guru ?? '-',
                        'kelas' => $item->kelas->nama_kelas ?? '-',
                        'mapel' => $item->mataPelajaran->nama_mapel ?? '-',
                    ];
                }),
            ];
        }

        return ['available' => true, 'bentrok' => collect()];
    }


    public static function getQuery($periode = null, $tingkat = null)
    {

        $query = JadwalPelajaran::query()
            ->with(['kelas', 'mataPelajaran', 'guru', 'kegiatan', 'jamPelajaran'])
            ->whereRelation('periode', 'id', $periode)
            ->withBentrok()
            ->withOverJp()
            ->orderByDesc('is_bentrok')
            ->orderByDesc('is_over_jp')
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");

        if ($tingkat) {
            $query->whereRelation('kelas', 'tingkat', $tingkat);
        }

        return $query;
    }

    public static function getKelasOptions(?string $tingkat = null, bool $showAllTingkat = true)
    {
        $options = $query = Kelas::orderByRaw("FIELD(kode_kelas, 'SMP', 'MA') DESC")
            ->orderBy('nama_kelas');

        if ($tingkat) {
            $query->where('tingkat', $tingkat);
        }

        if (!$showAllTingkat) {
            $query->whereNotIn('kode_kelas', ['SMP', 'MA']);
        }

        return $query
            ->get()
            ->map(fn($g) => [
                'value' => $g->id,
                'label' => $g->nama_kelas,
            ])
            ->toArray();

        return $options;
    }

    public static function getMapelOptions()
    {
            return MataPelajaran::orderBy('nama_mapel')
                ->get()
                ->map(fn($g) => ['value' => $g->id, 'label' => $g->nama_mapel])
                ->toArray();
    }

    public static function getMapelWithJpOptions($periodeId)
    {
        return MataPelajaran::orderBy('nama_mapel')
            ->withCount(['jadwal as jp_terpakai' => function ($q) use ($periodeId) {
                $q->where('periode_id', $periodeId);
            }])
            ->get()
            ->map(fn($g) => [
                'value' => $g->id,
                'label' => "{$g->nama_mapel} (JP Terpakai: {$g->jp_terpakai}/{$g->jp_per_pekan})",
            ])
            ->toArray();
    }

    public static function getPeriodeOptions()
    {
        return Cache::remember("periode_options", 60 * 60, function () {
            return Periode::orderBy('tahun_ajaran')
                ->get()
                ->map(fn($g) => ['value' => $g->id, 'label' => $g->tahun_ajaran])
                ->toArray();
        });
    }

    public static function getFirstPeriode()
    {
        return Periode::orderBy('tahun_ajaran')->first();
    }

    public static function getHariOptions(bool $includeAll = false)
    {
        $days = collect(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
        $days = $days
            ->map(fn($hari) => ['label' => $hari, 'value' => $hari])
            ->toArray();

        if ($includeAll) {
            $days = [
                ['label' => 'Semua Hari', 'value' => ''],
                ...$days
            ];
        }

        return $days;
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

    public static function getJamPelajaranOptions()
    {
        return \App\Models\JamPelajaran::orderBy('jam_mulai')
            ->get()
            ->map(fn($j) => [
                'value' => $j->id,
                'label' => "{$j->urutan} ({$j->jam_mulai} - {$j->jam_selesai})",
            ])
            ->toArray();
    }

    public static function getTahunAjaran($id)
    {
        $periode = Periode::find($id);
        return $periode ? $periode->tahun_ajaran : null;
    }

    public static function getCurrentDay()
    {
        return now()->translatedFormat('l');
    }

    public static function empty_to_null(array $data): array
    {
        return array_map(fn($v) => $v === '' ? null : $v, $data);
    }

    /**
     * Cek apakah JP mapel masih tersedia
     * 
     * @return bool
     */
    public static function jpAvailable($mataPelajaranId, $periodeId)
    {
        $mapel = MataPelajaran::find($mataPelajaranId);

        if (!$mapel) {
            return false;
        }

        // Hitung JP terpakai
        $jpTerpakai = JadwalPelajaran::where('mata_pelajaran_id', $mataPelajaranId)
            ->where('periode_id', $periodeId)
            ->count();

        // Jika jp_per_pekan = 0 maka tidak punya batas
        if ($mapel->jp_per_pekan == 0) {
            return true;
        }

        return $jpTerpakai < $mapel->jp_per_pekan;
    }

    /**
     * Cek dan throw error jika JP sudah habis
     */
    public static function validateJp($mataPelajaranId, $periodeId)
    {
        if (!self::jpAvailable($mataPelajaranId, $periodeId)) {
            $mapel = MataPelajaran::find($mataPelajaranId);

            return [
                'valid' => false,
                'message' => "Jatah JP untuk mapel {$mapel->nama_mapel} sudah habis.",
            ];
        }

        return [
            'valid' => true,
            'message' => '',
        ];
    }
}
