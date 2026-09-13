<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    public static bool $disableLogging = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'module',
        'subject_type',
        'subject_id',
        'description',
        'properties',
    ];

    /**
     * Disable model activity logging during bulk operations (e.g., Excel imports)
     */
    public static function withoutLogs(callable $callback): mixed
    {
        static::$disableLogging = true;
        try {
            return $callback();
        } finally {
            static::$disableLogging = false;
        }
    }

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to log custom activity easily
     */
    public static function record(
        string $action,
        string $description,
        ?string $module = null,
        ?Model $subject = null,
        ?array $properties = null
    ): static {
        $user = auth()->user();

        return static::create([
            'user_id' => $user?->id,
            'user_name' => $user?->nama ?? $user?->name ?? $user?->username ?? 'System',
            'action' => strtolower($action),
            'module' => $module ?? ($subject ? class_basename($subject) : 'General'),
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    public function getShortDescriptionAttribute(): string
    {
        return static::formatShortDescription($this->description);
    }

    public static function formatShortDescription(?string $desc): string
    {
        if (!$desc) {
            return '-';
        }

        if (!str_contains($desc, '|')) {
            return $desc;
        }

        $parts = array_map('trim', explode('|', $desc));
        if (count($parts) >= 3) {
            $part0 = $parts[0];

            // 1. Horizontal Save / Delete case
            if (stripos($part0, 'Horizontal') !== false) {
                $lastPart = end($parts);
                $kelas = '';
                if (preg_match('/ke\s+(\d+\s*kelas)/i', $lastPart, $km)) {
                    $kelas = ucwords(trim($km[1]));
                } elseif (preg_match('/\((SMP[^\)]*|MA[^\)]*)\)/i', $part0, $tm)) {
                    $kelas = trim($tm[1]);
                }
                $mapel = trim(explode('(', explode('ke ', $lastPart)[0])[0]);
                $actionPrefix = (stripos($part0, 'Hapus') !== false || stripos($part0, 'Menghapus') !== false)
                    ? 'Hapus Jadwal'
                    : 'Simpan Jadwal';
                return "{$actionPrefix} {$mapel}" . ($kelas ? " ({$kelas})" : '');
            }

            // 2. Standard schedule entries
            $action = 'Jadwal';
            if (stripos($part0, 'Hapus') !== false || stripos($part0, 'Menghapus') !== false) {
                $action = 'Hapus Jadwal';
            } elseif (stripos($part0, 'Ubah') !== false || stripos($part0, 'Mengubah') !== false) {
                $action = 'Ubah Jadwal';
            } elseif (stripos($part0, 'Tambah') !== false || stripos($part0, 'Menambah') !== false) {
                $action = 'Tambah Jadwal';
            }

            // Class name from part0 (after the last colon)
            $lastColon = strrpos($part0, ':');
            $kelas = $lastColon !== false ? trim(substr($part0, $lastColon + 1)) : $part0;

            // Mapel name from part 2 (before '(')
            $mapel = trim(explode('(', $parts[2])[0]);

            return "{$action} {$mapel} ({$kelas})";
        }

        return $desc;
    }

    public function formatLabel(string $key): string
    {
        $labels = [
            'kelas_id' => 'Kelas',
            'guru_id' => 'Guru Pengajar',
            'mata_pelajaran_id' => 'Mata Pelajaran',
            'jam_pelajaran_id' => 'Jam Pelajaran',
            'periode_id' => 'Tahun Ajaran / Periode',
            'hari' => 'Hari',
            'urutan' => 'Urutan Jam',
            'jam_mulai' => 'Jam Mulai',
            'jam_selesai' => 'Jam Selesai',
            'nama' => 'Nama',
            'nama_guru' => 'Nama Guru',
            'nama_kelas' => 'Nama Kelas',
            'nama_mapel' => 'Nama Mata Pelajaran',
            'kode_kelas' => 'Kode Kelas',
            'tingkat' => 'Tingkat',
            'tahun_ajaran' => 'Tahun Ajaran',
            'username' => 'Username',
            'email' => 'Email',
            'role' => 'Role Access',
            'is_active' => 'Status Aktif',
            'warna' => 'Warna Identitas',
            'Durasi Jam' => 'Durasi Jam (JP)',
            'Kelas' => 'Kelas',
            'Mata Pelajaran' => 'Mata Pelajaran',
            'Guru Pengajar' => 'Guru Pengajar',
            'Hari' => 'Hari',
            'Jam' => 'Jam Pelajaran',
            'Jam Pelajaran' => 'Jam Pelajaran',
            'Target Kelas' => 'Target Kelas',
            'Total Kelas' => 'Total Kelas',
            'Aksi' => 'Jenis Aksi',
            'Jumlah Data' => 'Jumlah Data',
            'count' => 'Total Terhapus',
            'ids' => 'ID Data',
            'Daftar Jadwal' => 'Daftar Jadwal Terhapus',
            'Total Jadwal Terhapus' => 'Total Jadwal Terhapus',
            'Kelas Terkait' => 'Kelas Terkait',
            'Mata Pelajaran Terkait' => 'Mata Pelajaran Terkait',
        ];
        return $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    public function formatValue(string $key, mixed $value): string
    {
        if (is_null($value) || $value === '') return '-';

        if ($key === 'kelas_id') {
            return \App\Models\Kelas::find($value)?->nama_kelas ?? "Kelas #{$value}";
        }
        if ($key === 'guru_id') {
            return \App\Models\Guru::find($value)?->nama_guru ?? "Guru #{$value}";
        }
        if ($key === 'mata_pelajaran_id') {
            return \App\Models\MataPelajaran::find($value)?->nama_mapel ?? "Mapel #{$value}";
        }
        if ($key === 'jam_pelajaran_id') {
            $j = \App\Models\JamPelajaran::find($value);
            return $j ? (is_numeric($j->urutan) ? "Jam {$j->urutan} ({$j->jam_mulai} - {$j->jam_selesai})" : "{$j->urutan} ({$j->jam_mulai} - {$j->jam_selesai})") : "Jam #{$value}";
        }
        if ($key === 'periode_id') {
            return \App\Models\Periode::find($value)?->tahun_ajaran ?? "Periode #{$value}";
        }
        if ($key === 'is_active') {
            return $value ? 'Aktif' : 'Non-Aktif';
        }
        if ($key === 'count') {
            return "{$value} Data Jadwal";
        }
        if ($key === 'ids' && is_array($value)) {
            return implode(', ', array_map(fn($id) => "#{$id}", $value));
        }

        if (is_array($value)) return implode(', ', $value);

        return (string) $value;
    }
}

