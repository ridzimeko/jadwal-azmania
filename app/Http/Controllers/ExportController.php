<?php

namespace App\Http\Controllers;

use App\Exports\JadwalExport;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use App\Models\JadwalPelajaran;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportJadwal(Request $request)
    {
        $type = $request->query('type', 'excel'); // default excel
        $jadwal = JadwalPelajaran::with(['guru', 'mataPelajaran', 'kelas'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');
        // Ambil daftar kelas unik
        $kelasList = \App\Models\Kelas::pluck('nama_kelas')->toArray();

        if ($type === 'pdf') {
            // Render view jadwal.blade.php
            $html = view('exports.jadwal', compact('jadwal', 'kelasList'))->render();

            $mpdf = new Mpdf([
                'format' => [210, 330],
                'orientation' => 'L',
                'default_font_size' => 10,
                'default_font' => 'dejavusans',
            ]);

            $mpdf->WriteHTML($html);
            return $mpdf->Output('Jadwal Pelajaran.pdf', 'I'); // tampil di browser
        }

        return Excel::download(new JadwalExport, 'jadwal.xlsx');
    }
}
