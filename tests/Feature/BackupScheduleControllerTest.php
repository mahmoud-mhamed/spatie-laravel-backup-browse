<?php

namespace MahmoudMhamed\BackupBrowse\Tests\Feature;

use MahmoudMhamed\BackupBrowse\Models\BackupSchedule;
use MahmoudMhamed\BackupBrowse\Tests\TestCase;

class BackupScheduleControllerTest extends TestCase
{
    public function test_index_displays_schedules()
    {
        BackupSchedule::create([
            'name' => 'Daily Backup',
            'frequency' => 'daily',
            'time' => '02:00',
        ]);

        $response = $this->get(route('backup-browse.schedules.index'));

        $response->assertStatus(200);
        $response->assertSee('Daily Backup');
    }

    public function test_create_shows_form()
    {
        $response = $this->get(route('backup-browse.schedules.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Schedule');
    }

    public function test_store_creates_schedule()
    {
        $response = $this->post(route('backup-browse.schedules.store'), [
            'name' => 'Nightly Backup',
            'frequency' => 'daily',
            'time' => '03:00',
            'enabled' => '1',
        ]);

        $response->assertRedirect(route('backup-browse.schedules.index'));
        $this->assertDatabaseHas('backup_schedules', [
            'name' => 'Nightly Backup',
            'frequency' => 'daily',
            'time' => '03:00',
            'enabled' => true,
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->post(route('backup-browse.schedules.store'), []);

        $response->assertSessionHasErrors(['name', 'frequency']);
    }

    public function test_store_rejects_invalid_frequency()
    {
        $response = $this->post(route('backup-browse.schedules.store'), [
            'name' => 'Invalid Backup',
            'frequency' => 'custom',
        ]);

        $response->assertSessionHasErrors(['frequency']);
    }

    public function test_edit_shows_form_with_schedule()
    {
        $schedule = BackupSchedule::create([
            'name' => 'Weekly Backup',
            'frequency' => 'weekly',
            'time' => '04:00',
            'day_of_week' => 1,
        ]);

        $response = $this->get(route('backup-browse.schedules.edit', $schedule));

        $response->assertStatus(200);
        $response->assertSee('Weekly Backup');
    }

    public function test_update_modifies_schedule()
    {
        $schedule = BackupSchedule::create([
            'name' => 'Old Name',
            'frequency' => 'daily',
            'time' => '02:00',
        ]);

        $response = $this->put(route('backup-browse.schedules.update', $schedule), [
            'name' => 'New Name',
            'frequency' => 'weekly',
            'time' => '05:00',
            'day_of_week' => 3,
            'enabled' => '1',
        ]);

        $response->assertRedirect(route('backup-browse.schedules.index'));
        $this->assertDatabaseHas('backup_schedules', [
            'id' => $schedule->id,
            'name' => 'New Name',
            'frequency' => 'weekly',
            'day_of_week' => 3,
        ]);
    }

    public function test_destroy_deletes_schedule()
    {
        $schedule = BackupSchedule::create([
            'name' => 'To Delete',
            'frequency' => 'daily',
            'time' => '02:00',
        ]);

        $response = $this->delete(route('backup-browse.schedules.destroy', $schedule));

        $response->assertRedirect(route('backup-browse.schedules.index'));
        $this->assertDatabaseMissing('backup_schedules', ['id' => $schedule->id]);
    }
}
