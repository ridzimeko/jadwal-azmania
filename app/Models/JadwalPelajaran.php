<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    /** @use HasFactory<\Database\Factories\JadwalPelajaranFactory> */
    use HasFactory, LogsActivity;

    public function getLogDisplayName(): string
    {
        $mapel = $this->mataPelajaran?->nama_mapel ?? 'Mapel #' . $this->mata_pelajaran_id;
        $kelas = $this->kelas?->nama_kelas ?? 'Kelas #' . $this->kelas_id;
        return "{$this->hari} - {$mapel} ({$kelas})";
    }

    protected $fillable = [
        'hari',
        'kelas_id',
        'mata_pelajaran_id',
        'jam_pelajaran_id',
        'guru_id',
        'periode_id'
    ];

    protected $table = 'jadwal_pelajaran';

    public function scopeOrderByHari($query)
    {
        return $query->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");
    }

    public function scopeHariIni($query)
    {
        $hariSekarang = Carbon::now()->translatedFormat('l');
        return $query->where('hari', $hariSekarang);
    }

    public function scopeWithBentrok($query)
    {
        return $query
            ->with(['jamPelajaran'])
            ->addSelect([
                'is_bentrok' => function ($sub) {
                    $sub->from('jadwal_pelajaran as j2')
                        ->selectRaw('COUNT(*) > 1')
                        ->join('jam_pelajaran as jp1', 'jp1.id', '=', 'j2.jam_pelajaran_id')
                        ->join('jam_pelajaran as jp2', 'jp2.id', '=', 'jadwal_pelajaran.jam_pelajaran_id')
                        ->whereColumn('j2.hari', 'jadwal_pelajaran.hari')
                        ->where(function ($q) {
                            $q->where(function ($yy) {
                                // bentrok guru
                                $yy->whereColumn('j2.guru_id', 'jadwal_pelajaran.guru_id')
                                    ->whereColumn('jp1.jam_mulai', '<', 'jp2.jam_selesai')
                                    ->whereColumn('jp1.jam_selesai', '>', 'jp2.jam_mulai');
                            })
                                ->orWhere(function ($yy) {
                                    // bentrok kelas
                                    $yy->whereColumn('j2.kelas_id', 'jadwal_pelajaran.kelas_id')
                                        ->whereColumn('jp1.jam_mulai', '<', 'jp2.jam_selesai')
                                        ->whereColumn('jp1.jam_selesai', '>', 'jp2.jam_mulai');
                                });
                        });
                }
            ]);
    }

    public function getKelasNamaAttribute()
    {
        return $this->kelas ? $this->kelas->nama_kelas : '-';
    }

    public function getGuruNamaAttribute()
    {
        return $this->guru?->nama_guru ?? '-';
    }

    public function getMapelNamaAttribute()
    {
        return $this->mataPelajaran?->nama_mapel ?? '-';
    }

    public function jamPelajaran()
    {
        return $this->belongsTo(JamPelajaran::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }
}
