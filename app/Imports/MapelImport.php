<?php

namespace App\Imports;

use App\Models\MataPelajaran;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class MapelImport implements ToModel, WithHeadingRow, WithUpserts, SkipsOnError
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    use SkipsErrors;

    public function model(array $row)
    {
        $jenis_mapel = strtolower($row['jenis_mapel'] ?? '');
        $jenis_mapel_option = [
            'kbm' => 'KBM',
            'non kbm' => 'Non KBM',
        ];

        return new MataPelajaran([
            'kode_mapel' => $row['kode_mapel'] ?? null,
            'warna' => $row['warna'] ?? '#ffffff',
            'jenis_mapel' => $jenis_mapel_option[$jenis_mapel] ?? null,
            'nama_mapel' => $row['mata_pelajaran'] ?? null,
        ]);
    }

    public function uniqueBy()
    {
        return 'kode_mapel';
    }
}
