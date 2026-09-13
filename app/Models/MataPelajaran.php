<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MataPelajaran extends Model
{
    /** @use HasFactory<\Database\Factories\MataPelajaranFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'jenis_mapel',
    ];

    protected $table = 'mata_pelajaran';

    public $timestamps = false;

    public function jadwal()
    {
        return $this->hasMany(JadwalPelajaran::class, 'mata_pelajaran_id');
    }
}
