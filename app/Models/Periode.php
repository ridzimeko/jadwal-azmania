<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    /** @use HasFactory<\Database\Factories\PeriodeFactory> */
    use HasFactory;

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'aktif',
    ];

    protected $table = 'periode';

    public function scopeGetTahunAjaran($query, $id) {
        return $query->find($id)->first()->tahun_ajaran;
    }
}
