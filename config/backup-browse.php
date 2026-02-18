<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware that will be applied to all backup-browse routes.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for all backup-browse routes.
    |
    */
    'route_prefix' => 'backup-browse',

    /*
    |--------------------------------------------------------------------------
    | Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk used to store backups.
    |
    */
    'disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Set this to your application's layout view (e.g. 'layouts.app') to
    | embed backup-browse views inside your own layout. When null, the
    | package's standalone layout with Tailwind CDN is used.
    |
    */
    'layout' => null,

    /*
    |--------------------------------------------------------------------------
    | Content Section
    |--------------------------------------------------------------------------
    |
    | The @yield / @section name used in your host layout.
    |
    */
    'content_section' => 'content',

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Queue connection and queue name for backup jobs. Defaults to 'sync'
    | so backups run immediately. Set to 'database', 'redis', etc. for
    | async processing (requires a queue worker: php artisan queue:work).
    |
    */
    'queue_connection' => 'sync',
    'queue' => null,

    /*
    |--------------------------------------------------------------------------
    | Allowed Backup Types
    |--------------------------------------------------------------------------
    |
    | Control which backup types are available in the UI.
    |
    */
    'allow_full_backup' => true,
    'allow_db_only_backup' => true,
    'allow_files_only_backup' => true,

];
