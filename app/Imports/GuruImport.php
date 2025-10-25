<?php

namespace App\Imports;

use App\Models\Guru;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class GuruImport implements ToModel, WithHeadingRow, WithUpserts, SkipsOnError
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    use SkipsErrors;

    public function model(array $row)
    {
        return new Guru([
            'nip' => $row['nip'],
            'nama_guru' => $row['nama_guru'],
            'warna' => $row['warna'] ?? '#ffffff',
        ]);
    }

    public function uniqueBy()
    {
        return 'nip';
    }
}
