<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaranItem extends Model
{
    /** @use HasFactory<\Database\Factories\JadwalPelajaranItemFactory> */
    use HasFactory;

    protected $fillable = [
        'hari',
        'jam_mulai',
        'jam_selesai',
        'kelas_id',
        'mata_pelajaran_id',
        'guru_id',
    ];

    protected $table = 'item_jadwal_pelajaran';

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
        return $query->addSelect([
            'is_bentrok' => self::query()
                ->selectRaw('COUNT(*) > 1')
                ->from('jadwal_pelajaran as j2')
                ->where(function ($q) {
                    $q
                        // Bentrok guru: guru sama, hari sama, jam tumpang tindih
                        ->whereColumn('j2.guru_id', 'jadwal_pelajaran.guru_id')
                        ->whereColumn('j2.hari', 'jadwal_pelajaran.hari')
                        ->whereColumn('j2.jam_mulai', '<', 'jadwal_pelajaran.jam_selesai')
                        ->whereColumn('j2.jam_selesai', '>', 'jadwal_pelajaran.jam_mulai');
                })
                ->orWhere(function ($q) {
                    $q
                        // Mapel double: mapel sama, kelas sama, jam tumpang tindih
                        ->whereColumn('j2.kelas_id', 'jadwal_pelajaran.kelas_id')
                        ->whereColumn('j2.hari', 'jadwal_pelajaran.hari')
                        ->whereColumn('j2.jam_mulai', '<', 'jadwal_pelajaran.jam_selesai')
                        ->whereColumn('j2.jam_selesai', '>', 'jadwal_pelajaran.jam_mulai');
                })
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

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }
}
