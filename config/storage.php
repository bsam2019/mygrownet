<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | GrowStream and the platform store file objects on Wasabi (S3-compatible).
    | DigitalOcean Spaces has been discontinued and removed.
    |
    */

    'migration_mode' => env('STORAGE_MIGRATION_MODE', 'wasabi_only'),

    /*
    |--------------------------------------------------------------------------
    | Storage Disks
    |--------------------------------------------------------------------------
    |
    | Define the primary storage disk.
    |
    */

    'disks' => [
        'primary' => env('STORAGE_PRIMARY_DISK', 'wasabi'),
        'secondary' => null,
    ],

];
