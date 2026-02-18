<?php

namespace MahmoudMhamed\BackupBrowse\Console\Commands;

use Illuminate\Console\Command;
use MahmoudMhamed\BackupBrowse\Jobs\CreateBackupJob;
use MahmoudMhamed\BackupBrowse\Models\Backup;
use MahmoudMhamed\BackupBrowse\Models\BackupSchedule;

class RunScheduledBackups extends Command
{
    protected $signature = 'backup-browse:run-scheduled';

    protected $description = 'Run all due backup schedules';

    public function handle(): int
    {
        $schedules = BackupSchedule::enabled()->get();
        $dispatched = 0;

        foreach ($schedules as $schedule) {
            if (! $schedule->isDue()) {
                continue;
            }

            $type = $schedule->only_db ? Backup::TYPE_DB : ($schedule->only_files ? Backup::TYPE_FILES : Backup::TYPE_FULL);

            $backup = Backup::create([
                'name' => $schedule->name . ' - ' . now()->format('Y-m-d H:i:s'),
                'disk' => config('backup-browse.disk'),
                'type' => $type,
                'status' => Backup::STATUS_PENDING,
            ]);

            CreateBackupJob::dispatch($backup, $schedule->only_db, $schedule->only_files);

            $schedule->update(['last_run_at' => now()]);
            $dispatched++;

            $this->info("Dispatched backup for schedule: {$schedule->name}");
        }

        $this->info("Done. Dispatched {$dispatched} backup(s).");

        return self::SUCCESS;
    }
}
