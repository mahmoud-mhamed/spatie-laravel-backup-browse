<?php

namespace MahmoudMhamed\BackupBrowse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Backup extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    const TYPE_FULL = 'full';
    const TYPE_DB = 'db';
    const TYPE_FILES = 'files';

    protected $fillable = [
        'name',
        'path',
        'disk',
        'type',
        'size',
        'status',
        'created_by_id',
        'created_by_type',
        'error_message',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function createdBy(): MorphTo
    {
        return $this->morphTo('created_by');
    }

    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }
}
