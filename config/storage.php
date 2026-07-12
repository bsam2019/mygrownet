<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Migration Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the storage migration between providers.
    |
    | Supported modes:
    | - do_spaces_only: Use only DigitalOcean Spaces (default)
    | - dual_write: Write to both DO Spaces and Wasabi, read from primary
    | - wasabi_only: Use only Wasabi (with DO Spaces fallback for old files)
    |
    */

    'migration_mode' => env('STORAGE_MIGRATION_MODE', 'do_spaces_only'),

    /*
    |--------------------------------------------------------------------------
    | Storage Disks
    |--------------------------------------------------------------------------
    |
    | Define the primary and secondary disks for migration.
    |
    */

    'disks' => [
        'primary' => env('STORAGE_PRIMARY_DISK', 'do_spaces'),
        'secondary' => env('STORAGE_SECONDARY_DISK', 'wasabi'),
    ],

];
