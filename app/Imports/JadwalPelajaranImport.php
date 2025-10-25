<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class JadwalPelajaranImport implements ToModel, WithHeadingRow, WithUpserts, SkipsOnError
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    use Importable, SkipsErrors;

    public function model(array $row)
    {
        // dd($row);
        // cari ID berdasarkan kode
        $kelas = Kelas::where('kode_kelas', $row['kode_kelas'] ?? null)->first();
        $mapel = MataPelajaran::where('kode_mapel', $row['kode_mata_pelajaran'] ?? null)->first();
        $guru  = Guru::where('nip', $row['kode_guru_pengajar'] ?? null)->first();

        // validasi sederhana
        if (!$kelas || !$mapel || !$guru) {
            // bisa skip atau throw error
            dd([$kelas, $mapel, $guru]);
            return null;
        }

        return new JadwalPelajaran([
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => $row['hari'],
            'jam_mulai' => $row['jam_mulai'],
            'jam_selesai' => $row['jam_selesai'],
        ]);
    }

    public function uniqueBy()
    {
        return ['kelas_id', 'mata_pelajaran_id', 'guru_id', 'hari', 'jam_mulai'];
    }
}
