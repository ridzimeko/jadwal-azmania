<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    /** @use HasFactory<\Database\Factories\JadwalPelajaranFactory> */
    use HasFactory;

    protected $fillable = [
        'kelas_id',
        'periode_id',
    ];

    protected $table = 'jadwal_pelajaran';

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
