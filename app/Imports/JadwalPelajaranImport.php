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
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // cari ID berdasarkan kode
            $kelas = Kelas::where('kode_kelas', $row['kode_kelas'] ?? null)->first();
            $mapel = MataPelajaran::where('kode_mapel', $row['kode_mata_pelajaran'] ?? null)->first();
            $guru  = Guru::where('kode_guru', $row['kode_guru_pengajar'] ?? null)->first();
            $jamMapel = JamPelajaran::where('urutan', $row['jam_ke'] ?? null)->first();

            // skip kalau tidak ditemukan
            if (!$kelas || !$mapel || !$jamMapel) {
                continue;
            }

            $hari = ucfirst(strtolower($row['hari']));

            // update or create data
            $jadwal = JadwalPelajaran::updateOrCreate(
                [
                    'kelas_id' => $kelas->id,
                    'mata_pelajaran_id' => $mapel->id,
                    'guru_id' => $guru->id ?? null,
                    'hari' => $hari,
                    'jam_pelajaran_id' => $jamMapel->id,
                    'periode_id' => $this->periodeId,
                ],
            );

            if ($jadwal->wasRecentlyCreated || $jadwal->wasChanged()) {
                $this->importedCount++;
            }
        }

        // return total data yang diimport
        return [
            'total_imported' => $this->importedCount,
        ];
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

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}
