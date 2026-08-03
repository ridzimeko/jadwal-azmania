<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    /** @use HasFactory<\Database\Factories\JadwalTetapFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'kode_kegiatan',
        'nama_kegiatan',
        'global',
        'warna'
    ];

    protected $table = 'kegiatan';
}
