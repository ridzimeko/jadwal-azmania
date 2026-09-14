<?php

namespace App\Imports;

use App\Models\Kelas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public function collection(Collection $rows)
    {
        $existingKelas = Kelas::all()->keyBy(fn($k) => strtolower(trim($k->nama_kelas)));

        foreach ($rows as $row) {
            // Ambil nama kelas dari berbagai kemungkinan header (nama_kelas, kelas, nama)
            $namaKelas = trim($row['nama_kelas'] ?? $row['kelas'] ?? $row['nama'] ?? '');
            if ($namaKelas === '') {
                continue;
            }

            // Abaikan kolom 'kode' atau 'kode_kelas' jika ada
            $tingkat = strtoupper(trim($row['tingkat'] ?? ''));
            if (!in_array($tingkat, ['SMP', 'MA'])) {
                if (preg_match('/^(7|8|9|VII|VIII|IX)\b/i', $namaKelas) || stripos($namaKelas, 'SMP') !== false) {
                    $tingkat = 'SMP';
                } elseif (preg_match('/^(10|11|12|X|XI|XII)\b/i', $namaKelas) || stripos($namaKelas, 'MA') !== false || stripos($namaKelas, 'SMA') !== false) {
                    $tingkat = 'MA';
                } else {
                    $tingkat = 'SMP';
                }
            }

            $key = strtolower($namaKelas);
            $kelas = $existingKelas->get($key);
            if (!$kelas) {
                $kelas = new Kelas(['nama_kelas' => $namaKelas]);
                $existingKelas->put($key, $kelas);
            }
            $kelas->tingkat = $tingkat;
            $kelas->save();
        }
    }
}
