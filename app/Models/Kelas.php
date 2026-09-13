<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    /** @use HasFactory<\Database\Factories\KelasFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nama_kelas',
        'tingkat',
    ];

    protected $table = 'kelas';

    public $timestamps = false;

    public function scopeNoTingkat($query)
    {
        return $query->whereNotIn('nama_kelas', ['Tingkat SMP', 'Tingkat MA']);
    }
}
