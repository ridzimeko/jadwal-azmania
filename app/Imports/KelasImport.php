<?php

namespace App\Imports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class KelasImport implements ToModel, WithHeadingRow, WithUpserts, SkipsOnError
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    use SkipsErrors;

    public function model(array $row)
    {
        return new Kelas([
            'kode_kelas' => $row['kode_kelas'] ?? null,
            'nama_kelas' => $row['nama_kelas'] ?? null,
            'tingkat' => strtoupper($row['tingkat'] ?? ''),
        ]);
    }

    public function uniqueBy()
    {
        return 'kode_kelas';
    }
}
