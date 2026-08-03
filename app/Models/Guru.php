<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    /** @use HasFactory<\Database\Factories\GuruFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'kode_guru',
        'nama_guru',
        'warna',
    ];

    protected $table = 'guru';

    public $timestamps = false;
}
