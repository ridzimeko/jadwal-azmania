<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            if (!static::shouldLogActivity()) {
                return;
            }

            $module = static::getActivityModuleLabel();
            $name = static::getActivityDisplayName($model);
            $new = static::filterLogAttributes($model->getAttributes());

            ActivityLog::record(
                action: 'create',
                description: "Menambah data {$module}: {$name}",
                module: $module,
                subject: $model,
                properties: ['new' => $new]
            );
        });

        static::updated(function (Model $model) {
            if (!static::shouldLogActivity()) {
                return;
            }

            $changes = $model->getChanges();
            $ignored = static::getIgnoredLogAttributes();

            $oldValues = [];
            $newValues = [];

            foreach ($changes as $key => $newValue) {
                if (in_array($key, $ignored, true)) {
                    continue;
                }
                $oldValue = $model->getOriginal($key);
                if ($oldValue != $newValue) {
                    $oldValues[$key] = $oldValue;
                    $newValues[$key] = $newValue;
                }
            }

            if (empty($newValues)) {
                return;
            }

            $module = static::getActivityModuleLabel();
            $name = static::getActivityDisplayName($model);

            ActivityLog::record(
                action: 'update',
                description: "Mengubah data {$module}: {$name}",
                module: $module,
                subject: $model,
                properties: [
                    'old' => static::filterLogAttributes($oldValues),
                    'new' => static::filterLogAttributes($newValues),
                ]
            );
        });

        static::deleted(function (Model $model) {
            if (!static::shouldLogActivity()) {
                return;
            }

            $module = static::getActivityModuleLabel();
            $name = static::getActivityDisplayName($model);
            $old = static::filterLogAttributes($model->getAttributes());

            ActivityLog::record(
                action: 'delete',
                description: "Menghapus data {$module}: {$name}",
                module: $module,
                subject: $model,
                properties: ['old' => $old]
            );
        });
    }

    protected static function shouldLogActivity(): bool
    {
        if (ActivityLog::$disableLogging) {
            return false;
        }

        return true;
    }

    protected static function getActivityModuleLabel(): string
    {
        if (property_exists(static::class, 'activityModuleLabel')) {
            return static::$activityModuleLabel;
        }

        $classMap = [
            'User' => 'Admin / User',
            'JadwalPelajaran' => 'Jadwal Pelajaran',
            'MataPelajaran' => 'Mata Pelajaran',
            'JamPelajaran' => 'Jam Pelajaran',
            'Guru' => 'Guru',
            'Kelas' => 'Kelas',
            'Kegiatan' => 'Kegiatan',
            'Periode' => 'Periode Jadwal',
        ];

        $base = class_basename(static::class);
        return $classMap[$base] ?? $base;
    }

    protected static function getActivityDisplayName(Model $model): string
    {
        if (method_exists($model, 'getLogDisplayName')) {
            return $model->getLogDisplayName();
        }

        return $model->nama
            ?? $model->name
            ?? $model->username
            ?? $model->nama_guru
            ?? $model->nama_kelas
            ?? $model->nama_mapel
            ?? $model->tahun_ajaran
            ?? ("#" . $model->getKey());
    }

    protected static function filterLogAttributes(array $attributes): array
    {
        $ignored = static::getIgnoredLogAttributes();
        foreach ($ignored as $key) {
            unset($attributes[$key]);
        }
        return $attributes;
    }

    protected static function getIgnoredLogAttributes(): array
    {
        return [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'updated_at',
            'created_at',
        ];
    }
}
