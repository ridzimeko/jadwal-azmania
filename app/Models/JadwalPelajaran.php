<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    /** @use HasFactory<\Database\Factories\JadwalPelajaranFactory> */
    use HasFactory;

    protected $fillable = [
        'hari',
        'jam_mulai',
        'jam_selesai',
        'kelas_id',
        'mata_pelajaran_id',
        'guru_id',
    ];

    protected $table = 'jadwal_pelajaran';

    public function scopeOrderByHari($query)
    {
        return $query->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')");
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
}
