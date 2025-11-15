<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    /** @use HasFactory<\Database\Factories\JadwalPelajaranFactory> */
    use HasFactory;

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
            ->with(['jamPelajaran:id,urutan,jam_mulai,jam_selesai'])
            ->addSelect(['is_bentrok' => function ($sub) {
                $sub->selectRaw('COUNT(*) > 1')
                    ->from('jadwal_pelajaran as j2')
                    ->join('jam_pelajaran as jp1', 'jp1.id', '=', 'j2.jam_pelajaran_id')
                    ->join('jam_pelajaran as jp2', 'jp2.id', '=', 'jadwal_pelajaran.jam_pelajaran_id')
                    ->whereColumn('j2.hari', 'jadwal_pelajaran.hari')
                    ->where(function ($q) {
                        $q->whereColumn('j2.guru_id', 'jadwal_pelajaran.guru_id')
                            ->whereColumn('jp1.jam_mulai', '<', 'jp2.jam_selesai')
                            ->whereColumn('jp1.jam_selesai', '>', 'jp2.jam_mulai');
                    })
                    ->orWhere(function ($q) {
                        $q->whereColumn('j2.kelas_id', 'jadwal_pelajaran.kelas_id')
                            ->whereColumn('jp1.jam_mulai', '<', 'jp2.jam_selesai')
                            ->whereColumn('jp1.jam_selesai', '>', 'jp2.jam_mulai');
                    });
            }]);
    }

    public function scopeWithOverJp($query) {
        return $query
            ->with(['mataPelajaran'])
            ->addSelect(['is_over_jp' => function ($sub) {
                $sub->selectRaw('CASE WHEN mp.jp_per_pekan > 0 AND COUNT(jp2.id) > mp.jp_per_pekan THEN 1 ELSE 0 END')
                    ->from('jadwal_pelajaran as jp2')
                    ->join('mata_pelajaran as mp', 'mp.id', '=', 'jadwal_pelajaran.mata_pelajaran_id')
                    ->whereColumn('jp2.mata_pelajaran_id', 'jadwal_pelajaran.mata_pelajaran_id')
                    ->whereColumn('jp2.periode_id', 'jadwal_pelajaran.periode_id');
            }]);
    }

    public function jadwalMapelSama()
    {
        return $this->hasMany(JadwalPelajaran::class, 'mata_pelajaran_id', 'mata_pelajaran_id')
            ->whereColumn('periode_id', 'jadwal_pelajaran.periode_id');
    }

    public function scopeWithJpTerpakai($query)
    {
        return $query->withCount('jadwalMapelSama as jp_terpakai');
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
