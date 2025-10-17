<?php

namespace App\Http\Controllers;

use App\Exports\JadwalExport;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $tingkat = $request->query('tingkat', 'SMP');
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

        $pdf = Pdf::loadView('pdf.jadwal', [
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

    public function exportJadwal(Request $request)
    {
        $type = $request->query('type', 'excel'); // default excel
        $hari = 'Senin';
        $tingkat = $request->query('type', 'smp');

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('livewire.datatable.jadwal-matrix');

            return response()->streamDownload(
                fn() => print($pdf->output()),
                'laporan-bulanan.pdf'
            );
            // // Render view jadwal.blade.php
            // $html = Livewire::mount('datatable.jadwal-matrix', compact(''));
            // dd($html);
            // // $html = view('exports.jadwal', compact('tingkat'))->render();

            // $mpdf = new Mpdf([
            //     'format' => [210, 330],
            //     'orientation' => 'L',
            //     'default_font_size' => 10,
            //     'default_font' => 'dejavusans',
            // ]);

            // $mpdf->WriteHTML($html);
            // return $mpdf->Output('Jadwal Pelajaran.pdf', 'I'); // tampil di browser
        }

        return Excel::download(new JadwalExport, 'jadwal.xlsx');
    }
}
