<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    /** @use HasFactory<\Database\Factories\MataPelajaranFactory> */
    use HasFactory;

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'jenis_mapel',
        'warna'
    ];

    protected $table = 'mata_pelajaran';

    public $timestamps = false;
}
