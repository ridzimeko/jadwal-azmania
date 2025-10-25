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
        return new MataPelajaran([
            'kode_mapel' => $row['kode_mapel'],
            'nama_mapel' => $row['mata_pelajaran'],
        ]);
    }

    public function uniqueBy()
    {
        return 'kode_mapel';
    }
}
