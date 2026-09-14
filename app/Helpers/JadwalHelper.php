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
     * @param  int|array|null  $ignoreId  abaikan ID tertentu saat edit data
     * @return array
     */
    public static function isAvailable(array $data, int|array|null $ignoreId = null): array
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

        if (isset($data['periode_id']) && $data['periode_id']) {
            $query->where('periode_id', $data['periode_id']);
        }

        if ($ignoreId) {
            if (is_array($ignoreId)) {
                $query->whereNotIn('id', $ignoreId);
            } else {
                $query->where('id', '!=', $ignoreId);
            }
        }

        // Cek data kelas (aman walau null)
        $kelas = isset($data['kelas_id']) ? Kelas::find($data['kelas_id']) : null;
        $isTingkatUmum = in_array($kelas?->nama_kelas, ['Tingkat SMP', 'Tingkat MA']);

        // Kalau bukan Tingkat Umum (SMP/MA), tetap cek bentrok guru dan kelas
        if (!$isTingkatUmum) {
            $query->where(function ($q) use ($data) {
                $q->where('guru_id', $data['guru_id'])
                    ->orWhere('kelas_id', $data['kelas_id']);
            });
        } else {
            $query->whereRelation('kelas', 'tingkat', $kelas->tingkat);
        }

        $bentrok = $query->get();

        if ($bentrok->isNotEmpty()) {
            return [
                'available' => false,
                'bentrok' => $bentrok->map(function ($item) {
                    return [
                        'id' => $item->id,
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

    /**
     * Cari semua jam pelajaran yang tersedia (bebas bentrok) untuk kelas & guru tertentu pada hari tertentu.
     *
     * @param array $data ['hari', 'kelas_id', 'guru_id', 'periode_id']
     * @param int|array|null $ignoreId
     * @return \Illuminate\Support\Collection
     */
    public static function findAvailableSlots(array $data, int|array|null $ignoreId = null)
    {
        $allJam = \App\Models\JamPelajaran::orderBy('jam_mulai')->orderBy('urutan')->get();
        $available = collect();

        foreach ($allJam as $jam) {
            $testData = array_merge($data, [
                'jam_pelajaran_id' => $jam->id,
                'jam_mulai' => $jam->jam_mulai,
                'jam_selesai' => $jam->jam_selesai,
            ]);

            $chk = static::isAvailable($testData, $ignoreId);
            if ($chk['available']) {
                $available->push([
                    'id' => (string) $jam->id,
                    'urutan' => $jam->urutan,
                    'jam_mulai' => $jam->jam_mulai,
                    'jam_selesai' => $jam->jam_selesai,
                    'label' => "Jam {$jam->urutan} ({$jam->jam_mulai} - {$jam->jam_selesai})",
                ]);
            }
        }

        return $available;
    }

    /**
     * Cari slot jam yang tersedia di hari-hari lain untuk kelas & guru tertentu.
     *
     * @param array $data ['kelas_id', 'guru_id', 'periode_id', 'hari']
     * @param int|array|null $ignoreId
     * @param int $requiredSlotsCount Jumlah slot jam pelajaran yang dibutuhkan (misal 2 JP)
     * @param string|null $currentHari Hari saat ini yang dikecualikan dari rekomendasi
     * @return array
     */
    /**
     * Helper untuk mencari blok jam pelajaran berurutan (consecutive) dari koleksi slot jam yang tersedia.
     */
    public static function findConsecutiveBlocks($available, int $requiredSlotsCount = 1): array
    {
        $consecutiveBlocks = [];
        if ($requiredSlotsCount > 1 && $available->count() >= $requiredSlotsCount) {
            $availableList = $available->values();
            $count = $availableList->count();
            for ($i = 0; $i <= $count - $requiredSlotsCount; $i++) {
                $block = [];
                $isConsecutive = true;
                for ($j = 0; $j < $requiredSlotsCount; $j++) {
                    $curr = $availableList[$i + $j];
                    $block[] = $curr;
                    if ($j > 0) {
                        $prev = $availableList[$i + $j - 1];
                        $prevUrutan = is_numeric($prev['urutan']) ? (int) $prev['urutan'] : null;
                        $currUrutan = is_numeric($curr['urutan']) ? (int) $curr['urutan'] : null;
                        if ($prevUrutan !== null && $currUrutan !== null) {
                            if ($currUrutan !== $prevUrutan + 1) {
                                $isConsecutive = false;
                                break;
                            }
                        } else {
                            if (substr($prev['jam_selesai'], 0, 5) !== substr($curr['jam_mulai'], 0, 5)) {
                                $isConsecutive = false;
                                break;
                            }
                        }
                    }
                }
                if ($isConsecutive) {
                    $slotIds = array_map(fn($s) => (string) $s['id'], $block);
                    $labels = array_map(fn($s) => is_numeric($s['urutan']) ? "Jam {$s['urutan']}" : $s['urutan'], $block);
                    $consecutiveBlocks[] = [
                        'slot_ids' => $slotIds,
                        'label' => implode(' & ', $labels),
                        'time' => substr($block[0]['jam_mulai'], 0, 5) . ' - ' . substr(end($block)['jam_selesai'], 0, 5),
                    ];
                }
            }
        } elseif ($requiredSlotsCount <= 1) {
            foreach ($available as $s) {
                $u = is_numeric($s['urutan']) ? "Jam {$s['urutan']}" : $s['urutan'];
                $consecutiveBlocks[] = [
                    'slot_ids' => [(string) $s['id']],
                    'label' => $u,
                    'time' => substr($s['jam_mulai'], 0, 5) . ' - ' . substr($s['jam_selesai'], 0, 5),
                ];
            }
        }

        return $consecutiveBlocks;
    }

    /**
     * Rekomendasi slot jam pelajaran cerdas:
     * 1. Memprioritaskan saran di hari yang ditentukan terlebih dahulu (current_day).
     * 2. Menyajikan hari-hari lain sebagai alternatif jika slot di hari tersebut tidak mencukupi atau ingin ganti hari (other_days).
     *
     * @param array $data ['kelas_id', 'guru_id', 'periode_id', 'hari']
     * @param int|array|null $ignoreId
     * @param int $requiredSlotsCount
     * @param string|null $currentHari
     * @return array ['current_day' => array|null, 'other_days' => array]
     */
    public static function getSmartScheduleRecommendations(array $data, int|array|null $ignoreId = null, int $requiredSlotsCount = 1, ?string $currentHari = null): array
    {
        $currentHari = $currentHari ?? ($data['hari'] ?? null);

        // 1. Prioritas Utama: Hari yang ditentukan saat ini
        $currentDayData = null;
        if ($currentHari) {
            $testCurrentData = array_merge($data, ['hari' => $currentHari]);
            $currentAvailable = static::findAvailableSlots($testCurrentData, $ignoreId);
            $currentBlocks = static::findConsecutiveBlocks($currentAvailable, $requiredSlotsCount);

            $currentDayData = [
                'hari' => $currentHari,
                'total_available' => $currentAvailable->count(),
                'slots' => $currentAvailable->toArray(),
                'consecutive_blocks' => $currentBlocks,
                'has_match' => !empty($currentBlocks),
            ];
        }

        // 2. Alternatif: Hari-hari lain
        $otherDays = static::findAvailableSlotsAcrossDays($data, $ignoreId, $requiredSlotsCount, $currentHari);

        return [
            'current_day' => $currentDayData,
            'other_days' => $otherDays,
        ];
    }

    /**
     * Cari slot jam yang tersedia di hari-hari lain untuk kelas & guru tertentu.
     *
     * @param array $data ['kelas_id', 'guru_id', 'periode_id', 'hari']
     * @param int|array|null $ignoreId
     * @param int $requiredSlotsCount Jumlah slot jam pelajaran yang dibutuhkan (misal 2 JP)
     * @param string|null $currentHari Hari saat ini yang dikecualikan dari rekomendasi
     * @return array
     */
    public static function findAvailableSlotsAcrossDays(array $data, int|array|null $ignoreId = null, int $requiredSlotsCount = 1, ?string $currentHari = null): array
    {
        $allDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $currentHari = $currentHari ?? ($data['hari'] ?? null);

        $recommendations = [];

        foreach ($allDays as $day) {
            if ($currentHari && strcasecmp($day, $currentHari) === 0) {
                continue;
            }

            $testData = array_merge($data, ['hari' => $day]);
            $available = static::findAvailableSlots($testData, $ignoreId);

            if ($available->isEmpty()) {
                continue;
            }

            $consecutiveBlocks = static::findConsecutiveBlocks($available, $requiredSlotsCount);

            $recommendations[] = [
                'hari' => $day,
                'total_available' => $available->count(),
                'slots' => $available->toArray(),
                'consecutive_blocks' => $consecutiveBlocks,
            ];
        }

        return $recommendations;
    }


    public static function getQuery($periode = null, $tingkat = null)
    {

        $query = JadwalPelajaran::query()
            ->with(['kelas', 'mataPelajaran', 'guru', 'kegiatan', 'jamPelajaran'])
            ->whereRelation('periode', 'id', $periode)
            ->withBentrok()
            ->orderByDesc('is_bentrok')
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 WHEN 'Minggu' THEN 7 ELSE 8 END");

        if ($tingkat && in_array(strtoupper($tingkat), ['SMP', 'MA'])) {
            $query->whereRelation('kelas', 'tingkat', strtoupper($tingkat));
        }

        return $query;
    }

    public static function getKelasOptions(?string $tingkat = null, bool $showAllTingkat = true)
    {
        $options = $query = Kelas::orderByRaw("CASE nama_kelas WHEN 'Tingkat SMP' THEN 1 WHEN 'Tingkat MA' THEN 2 ELSE 3 END")
            ->orderBy('nama_kelas');

        if ($tingkat && in_array(strtoupper($tingkat), ['SMP', 'MA'])) {
            $query->where('tingkat', strtoupper($tingkat));
        }

        if (!$showAllTingkat) {
            $query->noTingkat();
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
        return static::getMapelOptions();
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

    public static function getGuruOptions(bool $includeAll = false)
    {
        $options = Guru::orderBy('nama_guru')
            ->get()
            ->map(fn($g) => [
                'value' => $g->id,
                'label' => $g->nama_guru,
            ])
            ->toArray();

        if ($includeAll) {
            array_unshift($options, ['label' => 'Semua Guru', 'value' => '']);
        }

        return $options;
    }

    public static function getJamPelajaranOptions()
    {
        return \App\Models\JamPelajaran::orderBy('jam_mulai')
            ->get()
            ->map(fn($j) => [
                'value' => $j->id,
                'urutan' => $j->urutan,
                'jam_mulai' => $j->jam_mulai,
                'jam_selesai' => $j->jam_selesai,
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
}
