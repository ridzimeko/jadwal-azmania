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
}
