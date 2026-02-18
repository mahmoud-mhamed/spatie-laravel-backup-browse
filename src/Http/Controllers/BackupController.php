<?php

namespace MahmoudMhamed\BackupBrowse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use MahmoudMhamed\BackupBrowse\Jobs\CreateBackupJob;
use MahmoudMhamed\BackupBrowse\Models\Backup;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $query = Backup::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $perPage = in_array($request->input('per_page'), ['10', '25', '50', '100']) ? (int) $request->input('per_page') : 20;

        $backups = $query->paginate($perPage)->withQueryString();

        $backups->getCollection()->transform(function ($backup) {
            $backup->file_exists = false;

            if ($backup->path && $backup->disk) {
                $backup->file_exists = Storage::disk($backup->disk)->exists($backup->path);
            }

            return $backup;
        });

        return view('backup-browse::backups.index', compact('backups'));
    }

    public function run(Request $request)
    {
        $onlyDb = $request->boolean('only_db');
        $onlyFiles = $request->boolean('only_files');

        if ($onlyDb && ! config('backup-browse.allow_db_only_backup')) {
            return back()->with('error', 'Database-only backups are not allowed.');
        }

        if ($onlyFiles && ! config('backup-browse.allow_files_only_backup')) {
            return back()->with('error', 'Files-only backups are not allowed.');
        }

        if (! $onlyDb && ! $onlyFiles && ! config('backup-browse.allow_full_backup')) {
            return back()->with('error', 'Full backups are not allowed.');
        }

        $type = $onlyDb ? Backup::TYPE_DB : ($onlyFiles ? Backup::TYPE_FILES : Backup::TYPE_FULL);
        $label = $onlyDb ? 'DB Only' : ($onlyFiles ? 'Files Only' : 'Full');
        $backup = Backup::create([
            'name' => "{$label} Backup - " . now()->format('Y-m-d H:i:s'),
            'disk' => config('backup-browse.disk'),
            'type' => $type,
            'status' => Backup::STATUS_PENDING,
            'created_by_id' => $request->user()?->getKey(),
            'created_by_type' => $request->user() ? get_class($request->user()) : null,
        ]);

        CreateBackupJob::dispatch($backup, $onlyDb, $onlyFiles);

        return back()->with('success', 'Backup job has been dispatched.');
    }

    public function rename(Request $request, Backup $backup)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $backup->update(['name' => $request->input('name')]);

        return back()->with('success', 'Backup renamed successfully.');
    }

    public function download(Backup $backup)
    {
        if ($backup->status !== Backup::STATUS_COMPLETED || ! $backup->path) {
            return back()->with('error', 'This backup is not available for download.');
        }

        $disk = Storage::disk($backup->disk);

        if (! $disk->exists($backup->path)) {
            return back()->with('error', 'Backup file not found on disk.');
        }

        return $disk->download($backup->path, basename($backup->path));
    }

    public function destroy(Backup $backup)
    {
        $fileDeleted = false;

        if ($backup->path) {
            $disk = Storage::disk($backup->disk);

            if ($disk->exists($backup->path)) {
                $disk->delete($backup->path);
                $fileDeleted = true;
            }
        }

        $backup->delete();

        $message = $fileDeleted
            ? 'Backup and its file have been deleted from disk.'
            : 'Backup record has been deleted. No file was found on disk to remove.';

        return back()->with('success', $message);
    }

    public function destroySelected(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:backups,id'],
        ]);

        $backups = Backup::whereIn('id', $request->input('ids'))->get();
        $filesDeleted = 0;

        foreach ($backups as $backup) {
            if ($backup->path && $backup->disk) {
                $disk = Storage::disk($backup->disk);

                if ($disk->exists($backup->path)) {
                    $disk->delete($backup->path);
                    $filesDeleted++;
                }
            }

            $backup->delete();
        }

        $count = $backups->count();
        $message = "{$count} backup(s) deleted.";

        if ($filesDeleted > 0) {
            $message .= " {$filesDeleted} file(s) removed from disk.";
        }

        return back()->with('success', $message);
    }
}
