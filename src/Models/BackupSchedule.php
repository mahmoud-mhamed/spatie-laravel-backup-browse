<?php

namespace MahmoudMhamed\BackupBrowse\Models;

use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Model;

class BackupSchedule extends Model
{
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    protected $fillable = [
        'name',
        'frequency',
        'time',
        'day_of_week',
        'day_of_month',
        'only_db',
        'only_files',
        'enabled',
        'last_run_at',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'day_of_month' => 'integer',
        'only_db' => 'boolean',
        'only_files' => 'boolean',
        'enabled' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function toCronExpression(): string
    {
        [$hour, $minute] = explode(':', $this->time ?? '00:00');

        return match ($this->frequency) {
            self::FREQUENCY_DAILY => "{$minute} {$hour} * * *",
            self::FREQUENCY_WEEKLY => "{$minute} {$hour} * * " . ($this->day_of_week ?? 0),
            self::FREQUENCY_MONTHLY => "{$minute} {$hour} " . ($this->day_of_month ?? 1) . " * *",
            default => "{$minute} {$hour} * * *",
        };
    }

    public function isDue(): bool
    {
        $cron = new CronExpression($this->toCronExpression());

        return $cron->isDue(Carbon::now());
    }

    public function getNextRunDate(): Carbon
    {
        $cron = new CronExpression($this->toCronExpression());

        return Carbon::instance($cron->getNextRunDate());
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}
