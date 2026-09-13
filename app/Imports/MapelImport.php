<?php

namespace App\Imports;

use App\Models\MataPelajaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MapelImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Ambil nama mapel dari berbagai kemungkinan header (mata_pelajaran, nama_mapel, mapel, nama)
            $namaMapel = trim($row['mata_pelajaran'] ?? $row['nama_mapel'] ?? $row['nama_mata_pelajaran'] ?? $row['mapel'] ?? $row['nama'] ?? '');
            if ($namaMapel === '') {
                continue;
            }

            // Abaikan kolom 'kode' atau 'kode_mapel' serta 'jatah per pekan' / 'jp_per_pekan' jika ada
            $rawJenis = strtolower(trim($row['jenis_mapel'] ?? $row['jenis'] ?? ''));
            $jenisMapel = in_array($rawJenis, ['non kbm', 'non_kbm', 'non-kbm', 'nonkbm']) ? 'Non KBM' : 'KBM';

            $mapel = MataPelajaran::withTrashed()->firstOrNew(['nama_mapel' => $namaMapel]);
            $mapel->jenis_mapel = $jenisMapel;
            if ($mapel->trashed()) {
                $mapel->restore();
            }
            $mapel->save();
        }
    }
}
