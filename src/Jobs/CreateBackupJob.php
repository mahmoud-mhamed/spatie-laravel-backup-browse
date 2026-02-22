<?php

namespace MahmoudMhamed\BackupBrowse\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use MahmoudMhamed\BackupBrowse\Models\Backup;

class CreateBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Backup $backup,
        public bool $onlyDb = false,
        public bool $onlyFiles = false,
    ) {
        $connection = config('backup-browse.queue_connection');
        $queue = config('backup-browse.queue');

        if ($connection) {
            $this->onConnection($connection);
        }

        if ($queue) {
            $this->onQueue($queue);
        }
    }

    public function handle(): void
    {
        $this->backup->update(['status' => Backup::STATUS_IN_PROGRESS]);

        // Ensure common binary paths are available (fixes "mysqldump not found"
        // when running from web servers like Herd/Valet that have a limited PATH)
        $this->ensureBinaryPaths();

        try {
            $params = ['--disable-notifications' => true];

            if ($this->onlyDb) {
                $params['--only-db'] = true;
            }

            if ($this->onlyFiles) {
                $params['--only-files'] = true;
            }

            $exitCode = Artisan::call('backup:run', $params);
            $output = Artisan::output();

            if ($exitCode !== 0) {
                $this->backup->update([
                    'status' => Backup::STATUS_FAILED,
                    'error_message' => trim($output) ?: "backup:run exited with code {$exitCode}.",
                ]);

                return;
            }

            // Use spatie's config to find where backups are actually stored
            $destinationDisks = config('backup.backup.destination.disks', ['local']);
            $backupName = config('backup.backup.name', config('app.name', 'laravel-backup'));

            $newestFile = null;
            $foundDisk = null;

            foreach ($destinationDisks as $diskName) {
                $disk = Storage::disk($diskName);

                // Search with backup name as subdirectory
                $files = $disk->allFiles($backupName);

                // If nothing found, search the entire disk root
                if (empty($files)) {
                    $files = $disk->allFiles();
                }

                $candidate = collect($files)
                    ->filter(fn ($file) => str_ends_with($file, '.zip'))
                    ->sortByDesc(fn ($file) => $disk->lastModified($file))
                    ->first();

                if ($candidate) {
                    $newestFile = $candidate;
                    $foundDisk = $diskName;
                    break;
                }
            }

            if ($newestFile) {
                $this->backup->update([
                    'status' => Backup::STATUS_COMPLETED,
                    'path' => $newestFile,
                    'disk' => $foundDisk,
                    'size' => Storage::disk($foundDisk)->size($newestFile),
                ]);
            } else {
                $debugInfo = sprintf(
                    "Searched disks: [%s], backup name: '%s', artisan output: %s",
                    implode(', ', $destinationDisks),
                    $backupName,
                    trim($output)
                );

                Log::warning('BackupBrowse: ' . $debugInfo);

                $this->backup->update([
                    'status' => Backup::STATUS_FAILED,
                    'error_message' => "Backup completed but no zip file was found on disk. {$debugInfo}",
                ]);
            }
        } catch (\Throwable $e) {
            $this->backup->update([
                'status' => Backup::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ensure common binary paths are in the PATH environment variable.
     *
     * Web servers (Herd, Valet, etc.) often have a limited PATH that doesn't
     * include directories where database tools like mysqldump/pg_dump live.
     */
    protected function ensureBinaryPaths(): void
    {
        $currentPath = getenv('PATH') ?: '';

        $commonPaths = [
            '/opt/homebrew/bin',       // macOS (Apple Silicon) Homebrew
            '/usr/local/bin',          // macOS (Intel) Homebrew / Linux
            '/usr/local/mysql/bin',    // MySQL official installer (macOS)
            '/opt/homebrew/opt/mysql/bin',
            '/opt/homebrew/opt/mariadb/bin',
            '/opt/homebrew/opt/postgresql/bin',
            '/usr/bin',
        ];

        $pathsToAdd = [];
        foreach ($commonPaths as $path) {
            if (is_dir($path) && !str_contains($currentPath, $path)) {
                $pathsToAdd[] = $path;
            }
        }

        if ($pathsToAdd) {
            putenv('PATH=' . implode(PATH_SEPARATOR, $pathsToAdd) . PATH_SEPARATOR . $currentPath);
        }
    }
}
