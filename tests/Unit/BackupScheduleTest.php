<?php

namespace MahmoudMhamed\BackupBrowse\Tests\Unit;

use Carbon\Carbon;
use MahmoudMhamed\BackupBrowse\Models\BackupSchedule;
use MahmoudMhamed\BackupBrowse\Tests\TestCase;

class BackupScheduleTest extends TestCase
{
    public function test_daily_cron_expression()
    {
        $schedule = new BackupSchedule([
            'frequency' => 'daily',
            'time' => '03:30',
        ]);

        $this->assertEquals('30 03 * * *', $schedule->toCronExpression());
    }

    public function test_weekly_cron_expression()
    {
        $schedule = new BackupSchedule([
            'frequency' => 'weekly',
            'time' => '02:00',
            'day_of_week' => 1,
        ]);

        $this->assertEquals('00 02 * * 1', $schedule->toCronExpression());
    }

    public function test_monthly_cron_expression()
    {
        $schedule = new BackupSchedule([
            'frequency' => 'monthly',
            'time' => '04:15',
            'day_of_month' => 15,
        ]);

        $this->assertEquals('15 04 15 * *', $schedule->toCronExpression());
    }

    public function test_is_due_returns_true_when_due()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 6, 3, 0, 0)); // Monday 03:00

        $schedule = new BackupSchedule([
            'frequency' => 'weekly',
            'time' => '03:00',
            'day_of_week' => 1, // Monday
        ]);

        $this->assertTrue($schedule->isDue());

        Carbon::setTestNow();
    }

    public function test_is_due_returns_false_when_not_due()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 7, 3, 0, 0)); // Tuesday 03:00

        $schedule = new BackupSchedule([
            'frequency' => 'weekly',
            'time' => '03:00',
            'day_of_week' => 1, // Monday
        ]);

        $this->assertFalse($schedule->isDue());

        Carbon::setTestNow();
    }

    public function test_get_next_run_date_returns_carbon()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 6, 0, 0, 0));

        $schedule = new BackupSchedule([
            'frequency' => 'daily',
            'time' => '03:00',
        ]);

        $next = $schedule->getNextRunDate();

        $this->assertInstanceOf(Carbon::class, $next);
        $this->assertEquals('03:00', $next->format('H:i'));

        Carbon::setTestNow();
    }

    public function test_weekly_defaults_to_sunday()
    {
        $schedule = new BackupSchedule([
            'frequency' => 'weekly',
            'time' => '02:00',
        ]);

        $this->assertEquals('00 02 * * 0', $schedule->toCronExpression());
    }

    public function test_monthly_defaults_to_first()
    {
        $schedule = new BackupSchedule([
            'frequency' => 'monthly',
            'time' => '02:00',
        ]);

        $this->assertEquals('00 02 1 * *', $schedule->toCronExpression());
    }
}
