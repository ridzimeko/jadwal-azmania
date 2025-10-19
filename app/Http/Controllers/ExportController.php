<?php

namespace App\Http\Controllers;

use App\Exports\JadwalPelajaranExport;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $tingkat = strtoupper($request->query('tingkat', 'SMP'));
        $kelasList = Kelas::where('tingkat', $tingkat)
            ->orderBy('nama_kelas')
            ->get();

        $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

        // Ambil semua jadwal berdasarkan hari
        $jadwalPerHari = collect();
        foreach ($hariList as $hari) {
            $jadwal = JadwalPelajaran::with(['guru', 'mataPelajaran', 'kelas'])
                ->whereRelation('kelas', 'tingkat', $tingkat)
                ->where('hari', $hari)
                ->orderBy('jam_mulai')
                ->get()
                ->groupBy(function ($item) {
                    return $item->jam_mulai . ' - ' . $item->jam_selesai;
                });

            if ($jadwal->isNotEmpty()) {
                $jadwalPerHari[$hari] = $jadwal;
            }
        }

        // dd($tingkat);

        $pdf = Pdf::loadView('exports.jadwal-pelajaran-pdf', [
            'tingkat' => $tingkat,
            'kelasList' => $kelasList,
            'jadwalPerHari' => $jadwalPerHari
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("Jadwal-{$tingkat}.pdf");

        // return view('pdf.jadwal', [
        //     'tingkat' => $tingkat,
        //     'kelasList' => $kelasList,
        //     'jadwalPerHari' => $jadwalPerHari
        // ]);
    }

    public function exportExcel(Request $request)
    {
        $tingkat = strtoupper($request->query('tingkat', 'SMP'));
        return Excel::download(new JadwalPelajaranExport($tingkat), 'jadwal.xlsx');
    }
}
