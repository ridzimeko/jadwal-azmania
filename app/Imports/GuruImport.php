<?php

namespace App\Imports;

use App\Models\Guru;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToCollection, WithHeadingRow, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;

    public function collection(Collection $rows)
    {
        $existingGurus = Guru::withTrashed()->get()->keyBy(fn($g) => strtolower(trim($g->nama_guru)));

        foreach ($rows as $row) {
            // Ambil nama guru dari berbagai kemungkinan header (nama_guru, nama, guru)
            $namaGuru = trim($row['nama_guru'] ?? $row['nama'] ?? $row['guru'] ?? '');
            if ($namaGuru === '') {
                continue;
            }

            // Abaikan kolom 'kode' atau 'kode_guru' jika ada
            $warna = trim($row['warna'] ?? '');
            if ($warna === '' || !preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $warna)) {
                $warna = '#3b82f6';
            }

            $key = strtolower($namaGuru);
            $guru = $existingGurus->get($key);
            if (!$guru) {
                $guru = new Guru(['nama_guru' => $namaGuru]);
                $existingGurus->put($key, $guru);
            }

            $guru->warna = strtolower($warna);
            if ($guru->trashed()) {
                $guru->restore();
            }
            $guru->save();
        }
    }
}
