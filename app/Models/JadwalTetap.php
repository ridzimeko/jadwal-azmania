<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalTetap extends Model
{
    /** @use HasFactory<\Database\Factories\JadwalTetapFactory> */
    use HasFactory;

    protected $fillable = [
        'nama',
        'jam_mulai',
        'jam_selesai',
        'warna'
    ];

    protected $table = 'jadwal_tetap';
}
