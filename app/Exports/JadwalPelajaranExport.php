<?php

namespace App\Exports;

use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JadwalPelajaranExport implements FromView, WithTitle, WithDrawings
{
    protected $tingkat;

    public function __construct(string $tingkat)
    {
        $this->tingkat = $tingkat;
    }

    public function view(): View
    {
        $kelasList = Kelas::where('tingkat', $this->tingkat)
            ->whereNotIn('kode_kelas', ['SMP', 'MA'])
            ->orderBy('nama_kelas')
            ->get();

        $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

        // Ambil semua jadwal berdasarkan hari
        $jadwalPerHari = collect();
        foreach ($hariList as $hari) {
            $jadwal = JadwalPelajaran::with(['guru', 'mataPelajaran', 'kelas'])
                ->whereRelation('kelas', 'tingkat', $this->tingkat)
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

        return view('exports.jadwal-pelajaran-excel', [
            'tingkat' => $this->tingkat,
            'kelasList' => $kelasList,
            'jadwalPerHari' => $jadwalPerHari
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Kop Surat');
        $drawing->setDescription('Logo Sekolah');
        $drawing->setPath(public_path('images/logo.png')); // Ganti path logo kamu
        $drawing->setHeight(80);
        $drawing->setCoordinates('A1');

        return [$drawing];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getRowDimension(1)->setRowHeight(50);
        $sheet->mergeCells('A5:F5');

        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');

        return [];
    }

    public function title(): string
    {
        return 'Jadwal Pelajaran';
    }
}
