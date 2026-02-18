# Laravel Backup Browse

A web UI for managing Laravel backups and backup schedules, built on top of [spatie/laravel-backup](https://spatie.be/docs/laravel-backup/v9/introduction).

![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)
![Laravel](https://img.shields.io/badge/Laravel-10.x%20%7C%2011.x-red)
![License](https://img.shields.io/badge/License-MIT-green)

---

## Features

- **Backup Management** — View, create, download, and delete backups from a clean web interface
- **Scheduled Backups** — Create and manage backup schedules (daily, weekly, monthly)
- **One-Click Backups** — Run full, database-only, or files-only backups instantly
- **Queue Support** — Backup jobs are dispatched to your queue for non-blocking execution
- **Polymorphic Tracking** — Every backup records who created it
- **Custom Layout Support** — Use the built-in Tailwind layout or plug into your own
- **Publishable Assets** — Config, migrations, and views are all publishable

---

## Installation

```bash
composer require mahmoud-mhamed/laravel-backup-browse
```

The package auto-discovers its service provider. No manual registration needed.

### Publish Config (optional)

```bash
php artisan vendor:publish --tag=backup-browse-config
```

### Run Migrations

```bash
php artisan migrate
```

> Migrations are idempotent — running them twice won't cause errors.

### Set Up the Scheduler

Add this to your server's crontab if you haven't already:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

The package automatically registers its `backup-browse:run-scheduled` command to run every minute via Laravel's scheduler.

---

## Configuration

After publishing, edit `config/backup-browse.php`:

```php
return [
    // Middleware applied to all routes
    'middleware' => ['web', 'auth'],

    // URL prefix: yourdomain.com/backup-browse
    'route_prefix' => 'backup-browse',

    // Filesystem disk for storing backups
    'disk' => 'local',

    // Set to your app layout (e.g. 'layouts.app') or null for standalone
    'layout' => null,

    // @yield() section name in your layout
    'content_section' => 'content',

    // Queue settings (null = app defaults)
    'queue_connection' => null,
    'queue' => null,

    // Toggle which backup types are available
    'allow_full_backup' => true,
    'allow_db_only_backup' => true,
    'allow_files_only_backup' => true,
];
```

---

## Usage

### Web Interface

Navigate to `/backup-browse` in your browser.

**Backups tab** — View all backups with status badges, download completed backups, or delete old ones. Hit **Backup Now**, **DB Only**, or **Files Only** to trigger a new backup.

**Schedules tab** — Create, edit, and delete backup schedules. Choose from daily, weekly, or monthly frequencies. Toggle schedules on/off without deleting them.

### Artisan Commands

Run all due backup schedules manually:

```bash
php artisan backup-browse:run-scheduled
```

### Using Your Own Layout

Set the `layout` config to your app's layout view:

```php
'layout' => 'layouts.app',
'content_section' => 'content',
```

The package views will render inside your layout's `@yield('content')` section. When `layout` is `null`, a standalone layout with Tailwind CSS (via CDN) is used.

### Publish Views for Customization

```bash
php artisan vendor:publish --tag=backup-browse-views
```

Views will be copied to `resources/views/vendor/backup-browse/` where you can modify them freely.

---

## How It Works

```
User clicks "Backup Now"
        │
        ▼
BackupController creates a Backup record (status: pending)
        │
        ▼
CreateBackupJob is dispatched to queue
        │
        ▼
Job runs `php artisan backup:run` (spatie/laravel-backup)
        │
        ▼
Job finds the newest .zip on disk, updates Backup record
        │
        ▼
Backup status → completed (with path + size)
```

For scheduled backups, the `backup-browse:run-scheduled` command runs every minute, checks which schedules are due via cron expressions, and dispatches `CreateBackupJob` for each.

---

## Database Schema

### `backups`

| Column | Type | Description |
|---|---|---|
| `id` | bigint | Primary key |
| `name` | string | Backup name |
| `path` | string (nullable) | File path on disk |
| `disk` | string | Filesystem disk |
| `size` | bigint | Size in bytes |
| `status` | string | `pending`, `in_progress`, `completed`, `failed` |
| `created_by_id` | bigint (nullable) | Polymorphic creator ID |
| `created_by_type` | string (nullable) | Polymorphic creator type |
| `error_message` | text (nullable) | Error details if failed |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### `backup_schedules`

| Column | Type | Description |
|---|---|---|
| `id` | bigint | Primary key |
| `name` | string | Schedule name |
| `frequency` | string | `daily`, `weekly`, `monthly` |
| `time` | string | HH:MM format |
| `day_of_week` | tinyint (nullable) | 0 (Sun) – 6 (Sat) |
| `day_of_month` | tinyint (nullable) | 1 – 31 |
| `only_db` | boolean | Database-only backup |
| `only_files` | boolean | Files-only backup |
| `enabled` | boolean | Whether schedule is active |
| `last_run_at` | timestamp (nullable) | Last execution time |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

## Testing

```bash
composer test
```

Or directly:

```bash
vendor/bin/phpunit
```

Tests use Orchestra Testbench with SQLite in-memory. No external services required.

---

## Requirements

- PHP 8.2+
- Laravel 10.x or 11.x
- [spatie/laravel-backup](https://github.com/spatie/laravel-backup) v9 (installed automatically)

---

## License

MIT License. See [LICENSE](LICENSE) for details.
