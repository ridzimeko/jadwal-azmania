<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamPelajaran extends Model
{
    /** @use HasFactory<\Database\Factories\JamPelajaranFactory> */
    use HasFactory;

    protected $hidden = []; // atau pastikan 'urutan' tidak disembunyikan

    protected $fillable = [
        'jam_mulai',
        'jam_selesai',
        'urutan',
    ];

    protected $table = 'jam_pelajaran';

    public $timestamps = true;
}
