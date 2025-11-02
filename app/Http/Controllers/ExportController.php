<?php

namespace App\Http\Controllers;

use App\Exports\JadwalPelajaranExport;
use App\Helpers\JadwalHelper;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $tingkat = strtoupper($request->query('tingkat', 'SMP'));
        $periode = $request->query('periode');

        if (!$periode) return abort('400', 'Masukkan periode jadwal');

        // jika tingkat != smp atau ma
        if (!in_array($tingkat, ['SMP', 'MA'])) {
            abort(404, 'Jadwal tidak ditemukan');
        }

        $kelasList = Kelas::where('tingkat', $tingkat)
            ->whereNotIn('kode_kelas', ['SMP', 'MA'])
            ->orderBy('nama_kelas')
            ->get();

        $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

        // Ambil semua jadwal berdasarkan hari
        $jadwalPerHari = collect();
        foreach ($hariList as $hari) {
            $jadwal = JadwalHelper::getQuery($periode, $tingkat)
                ->where('hari', $hari)
                ->get()
                ->groupBy(function ($item) {
                    return $item->jam_mulai . ' - ' . $item->jam_selesai;
                });

            if ($jadwal->isNotEmpty()) {
                $jadwalPerHari[$hari] = $jadwal;
            }
        }

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
        $periode = $request->query('periode');

        if (!$periode) return abort('400', 'Masukkan periode jadwal');
        return Excel::download(new JadwalPelajaranExport($tingkat, $periode), "jadwal-{$tingkat}.xlsx");
    }
}