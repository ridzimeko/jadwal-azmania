<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class JadwalPelajaranImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected int $importedCount = 0;
    protected array $bentrokList = [];
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function collection(Collection $rows)
    {
        // Pre-fetch all master data for fast in-memory lookup
        $kelasList = Kelas::all()->keyBy(fn($item) => strtolower(trim($item->nama_kelas)));
        $mapelList = MataPelajaran::all()->keyBy(fn($item) => strtolower(trim($item->nama_mapel)));
        $guruList  = Guru::all()->keyBy(fn($item) => strtolower(trim($item->nama_guru)));
        $jamList   = JamPelajaran::all()->keyBy('urutan');

        foreach ($rows as $row) {
            // cari ID berdasarkan nama
            $kelasNama = trim($row['nama_kelas'] ?? $row['kelas'] ?? $row['kode_kelas'] ?? '');
            $kelas = $kelasNama !== '' ? ($kelasList->get(strtolower($kelasNama)) ?? Kelas::where('nama_kelas', $kelasNama)->first()) : null;

            $mapelNama = trim($row['nama_mata_pelajaran'] ?? $row['mata_pelajaran'] ?? $row['nama_mapel'] ?? $row['kode_mata_pelajaran'] ?? '');
            $mapel = $mapelNama !== '' ? ($mapelList->get(strtolower($mapelNama)) ?? MataPelajaran::where('nama_mapel', $mapelNama)->first()) : null;

            $guruNama = trim($row['nama_guru_pengajar'] ?? $row['nama_guru'] ?? $row['guru'] ?? $row['kode_guru_pengajar'] ?? '');
            $guru  = $guruNama !== '' ? ($guruList->get(strtolower($guruNama)) ?? Guru::where('nama_guru', $guruNama)->first()) : null;

            // skip kalau tidak ditemukan
            if (!$kelas || !$mapel) {
                continue;
            }

            // parse jam_ke menjadi array integer (misal "1-3,5" => [1,2,3,5])
            $jamNumbers = $this->parseJamKe($row['jam_ke'] ?? '');

            // jika tidak ada jam valid, skip baris ini
            if (empty($jamNumbers)) {
                continue;
            }

            $hari = ucfirst(strtolower($row['hari']));

            foreach ($jamNumbers as $jamNo) {
                // cari jam pelajaran berdasarkan urutan dari memori
                $jamMapel = $jamList->get($jamNo) ?? JamPelajaran::where('urutan', $jamNo)->first();

                // jika tidak ditemukan jam tertentu maka lewati jam itu
                if (!$jamMapel) {
                    continue;
                }

                $dataDb = [
                    'kelas_id' => $kelas->id,
                    'mata_pelajaran_id' => $mapel->id,
                    'guru_id' => $guru->id ?? null,
                    'hari' => $hari,
                    'jam_pelajaran_id' => $jamMapel->id,
                    'periode_id' => $this->periodeId,
                ];

                $dataCheck = array_merge($dataDb, [
                    'jam_mulai' => $jamMapel->jam_mulai,
                    'jam_selesai' => $jamMapel->jam_selesai,
                    'kelas_model' => $kelas,
                ]);

                $checkAvailability = \App\Helpers\JadwalHelper::isAvailable($dataCheck);
                if (!$checkAvailability['available']) {
                    foreach ($checkAvailability['bentrok'] as $b) {
                        $this->bentrokList[] = [
                            'kelas' => $b['kelas'],
                            'jam_mulai' => $b['jam_mulai'],
                            'jam_selesai' => $b['jam_selesai'],
                            'guru' => $b['guru'],
                            'mapel' => $b['mapel'],
                            'hari' => $hari,
                            'kelas_id' => $kelas->id,
                            'mata_pelajaran_id' => $mapel->id,
                            'guru_id' => $guru->id ?? null,
                            'periode_id' => $this->periodeId,
                            'data' => $dataDb,
                        ];
                    }
                }

                // update or create data
                $jadwal = JadwalPelajaran::updateOrCreate($dataDb);

                if ($jadwal->wasRecentlyCreated || $jadwal->wasChanged()) {
                    $this->importedCount++;
                }
            }
        }

        // return total data yang diimport
        return [
            'total_imported' => $this->importedCount,
        ];
    }

    public function getBentrokList(): array
    {
        return $this->bentrokList;
    }

    protected function parseExcelTime($value)
    {
        // Jika value berupa angka desimal (contoh: 0.25 = 6:00)
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('H:i');
        }

        // Kalau sudah string (misal "08:00")
        return date('H:i', strtotime($value));
    }

    /**
     * Parse string jam_ke menjadi array integer
     * Contoh input valid:
     * - "1-3"
     * - "2,6,7"
     * - "1-3,5,8-9"
     */
    protected function parseJamKe($value): array
    {
        if (is_null($value)) {
            return [];
        }

        // trim dan hilangkan spasi yang tidak perlu
        $value = trim((string)$value);
        $value = str_replace(' ', '', $value);

        if ($value === '') {
            return [];
        }

        $parts = explode(',', $value);
        $results = [];

        foreach ($parts as $part) {
            if ($part === '') continue;

            // rentang seperti "1-3"
            if (strpos($part, '-') !== false) {
                [$start, $end] = array_map('intval', explode('-', $part, 2));
                // pastikan start <= end
                if ($start > $end) {
                    // swap jika user memasukkan "3-1"
                    [$start, $end] = [$end, $start];
                }
                for ($i = $start; $i <= $end; $i++) {
                    $results[] = $i;
                }
            } else {
                // single number
                $num = intval($part);
                if ($num > 0) {
                    $results[] = $num;
                }
            }
        }

        // unikkan dan urutkan
        $results = array_values(array_unique($results));
        sort($results, SORT_NUMERIC);

        return $results;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}
