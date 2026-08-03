<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamPelajaran extends Model
{
    /** @use HasFactory<\Database\Factories\JamPelajaranFactory> */
    use HasFactory, LogsActivity;

    public function getLogDisplayName(): string
    {
        return "Jam Ke-{$this->urutan} ({$this->jam_mulai} - {$this->jam_selesai})";
    }

    protected $hidden = []; // atau pastikan 'urutan' tidak disembunyikan

    protected $fillable = [
        'jam_mulai',
        'jam_selesai',
        'urutan',
    ];

    protected $table = 'jam_pelajaran';

    public $timestamps = true;
}
