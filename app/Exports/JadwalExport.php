<?php

namespace App\Exports;

use App\Models\JadwalPelajaran;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithTitle;

class JadwalExport implements FromView, WithTitle
{
    public function view(): View
    {
        $jadwal = JadwalPelajaran::with(['kelas', 'guru', 'mapel'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');

        // Ambil daftar kelas unik
        $kelasList = \App\Models\Kelas::pluck('nama_kelas')->toArray();

        return view('exports.jadwal', [
            'jadwal' => $jadwal,
            'kelasList' => $kelasList,
        ]);
    }

    public function title(): string
    {
        return 'Jadwal Pelajaran';
    }
}
