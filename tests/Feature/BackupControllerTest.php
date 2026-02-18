<?php

namespace MahmoudMhamed\BackupBrowse\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use MahmoudMhamed\BackupBrowse\Jobs\CreateBackupJob;
use MahmoudMhamed\BackupBrowse\Models\Backup;
use MahmoudMhamed\BackupBrowse\Tests\TestCase;

class BackupControllerTest extends TestCase
{
    public function test_index_displays_backups()
    {
        Backup::create([
            'name' => 'Test Backup',
            'disk' => 'local',
            'status' => Backup::STATUS_COMPLETED,
            'size' => 1024,
        ]);

        $response = $this->get(route('backup-browse.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Backup');
    }

    public function test_run_dispatches_full_backup_job()
    {
        Queue::fake();

        $response = $this->post(route('backup-browse.run'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        Queue::assertPushed(CreateBackupJob::class, function ($job) {
            return $job->onlyDb === false && $job->onlyFiles === false;
        });
        $this->assertDatabaseHas('backups', ['status' => Backup::STATUS_PENDING]);
    }

    public function test_run_dispatches_db_only_backup_job()
    {
        Queue::fake();

        $response = $this->post(route('backup-browse.run'), ['only_db' => '1']);

        $response->assertRedirect();
        Queue::assertPushed(CreateBackupJob::class, function ($job) {
            return $job->onlyDb === true;
        });
    }

    public function test_run_dispatches_files_only_backup_job()
    {
        Queue::fake();

        $response = $this->post(route('backup-browse.run'), ['only_files' => '1']);

        $response->assertRedirect();
        Queue::assertPushed(CreateBackupJob::class, function ($job) {
            return $job->onlyFiles === true;
        });
    }

    public function test_run_rejects_disallowed_backup_type()
    {
        Queue::fake();
        config(['backup-browse.allow_full_backup' => false]);

        $response = $this->post(route('backup-browse.run'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        Queue::assertNotPushed(CreateBackupJob::class);
    }

    public function test_rename_updates_backup_name()
    {
        $backup = Backup::create([
            'name' => 'Old Name',
            'disk' => 'local',
            'status' => Backup::STATUS_COMPLETED,
        ]);

        $response = $this->put(route('backup-browse.rename', $backup), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('backups', [
            'id' => $backup->id,
            'name' => 'New Name',
        ]);
    }

    public function test_rename_validates_name_required()
    {
        $backup = Backup::create([
            'name' => 'Test',
            'disk' => 'local',
            'status' => Backup::STATUS_COMPLETED,
        ]);

        $response = $this->put(route('backup-browse.rename', $backup), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_download_completed_backup()
    {
        Storage::fake('local');
        Storage::disk('local')->put('backups/test.zip', 'fake-zip-content');

        $backup = Backup::create([
            'name' => 'Test Backup',
            'disk' => 'local',
            'status' => Backup::STATUS_COMPLETED,
            'path' => 'backups/test.zip',
            'size' => 16,
        ]);

        $response = $this->get(route('backup-browse.download', $backup));

        $response->assertStatus(200);
        $response->assertDownload('test.zip');
    }

    public function test_download_rejects_incomplete_backup()
    {
        $backup = Backup::create([
            'name' => 'Test Backup',
            'disk' => 'local',
            'status' => Backup::STATUS_PENDING,
        ]);

        $response = $this->get(route('backup-browse.download', $backup));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_destroy_deletes_backup_and_file()
    {
        Storage::fake('local');
        Storage::disk('local')->put('backups/test.zip', 'fake-zip-content');

        $backup = Backup::create([
            'name' => 'Test Backup',
            'disk' => 'local',
            'status' => Backup::STATUS_COMPLETED,
            'path' => 'backups/test.zip',
            'size' => 16,
        ]);

        $response = $this->delete(route('backup-browse.destroy', $backup));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('backups', ['id' => $backup->id]);
        Storage::disk('local')->assertMissing('backups/test.zip');
    }
}
