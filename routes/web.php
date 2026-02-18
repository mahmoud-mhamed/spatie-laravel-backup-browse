<?php

use Illuminate\Support\Facades\Route;
use MahmoudMhamed\BackupBrowse\Http\Controllers\BackupController;
use MahmoudMhamed\BackupBrowse\Http\Controllers\BackupScheduleController;

Route::group([
    'prefix' => config('backup-browse.route_prefix', 'backup-browse'),
    'middleware' => config('backup-browse.middleware', ['web', 'auth']),
    'as' => 'backup-browse.',
], function () {
    // Backup routes
    Route::get('/', [BackupController::class, 'index'])->name('index');
    Route::post('/run', [BackupController::class, 'run'])->name('run');
    Route::put('/{backup}/rename', [BackupController::class, 'rename'])->name('rename');
    Route::get('/download/{backup}', [BackupController::class, 'download'])->name('download');
    Route::delete('/destroy-selected', [BackupController::class, 'destroySelected'])->name('destroy-selected');
    Route::delete('/{backup}', [BackupController::class, 'destroy'])->name('destroy');

    // Schedule routes
    Route::resource('schedules', BackupScheduleController::class)->except(['show']);
});
