<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemJadwalTetap extends Model
{
    /** @use HasFactory<\Database\Factories\ItemJadwalTetapFactory> */
    use HasFactory;

    protected $fillable = [
        'jam_mulai',
        'jam_selesai',
        'nama_kegiatan',
    ];

    protected $table = 'item_jadwal_tetap';
}
