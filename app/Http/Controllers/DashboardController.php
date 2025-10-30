<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $columnDefs = [
        ['name' => 'Kelas', 'field' => 'kelas_nama'],
        ['name' => 'Hari', 'field' => 'hari'],
        ['name' => 'Jam Mulai', 'field' => 'jam_mulai'],
        ['name' => 'Jam Selesai', 'field' => 'jam_selesai'],
        ['name' => 'Mata Pelajaran', 'field' => 'mapel_nama'],
        ['name' => 'Guru Pengajar', 'field' => 'guru_nama'],
    ];

    public function index()
    {
        $latestPeriode = Periode::latest()->first();
        return view('dashboard', [
            'totalMataPelajaran' => MataPelajaran::count(),
            'totalKelas' => Kelas::count(),
            'totalGuru' => Guru::count(),
            'totalUsers' => User::count(),
            'jadwalPelajaran' => JadwalPelajaran::class,
            'columnDefs' => $this->columnDefs,
            'periode' => $latestPeriode ?? null,
        ]);
    }
}
