<?php

namespace App\Imports;

use App\Models\Guru;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class GuruImport implements ToModel, WithHeadingRow, WithUpserts, SkipsOnFailure, SkipsOnError
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    use Importable, SkipsFailures, SkipsErrors;

    public function model(array $row)
    {
        return new Guru([
            'nip' => $row['nip'],
            'nama_guru' => $row['nama_guru'],
        ]);
    }

    public function uniqueBy()
    {
        return 'nip';
    }
}
