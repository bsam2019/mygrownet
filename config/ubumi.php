<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ubumi Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Ubumi family lineage and health check-in platform
    |
    */

    'enabled' => env('UBUMI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Duplicate Detection
    |--------------------------------------------------------------------------
    |
    | Settings for duplicate person detection
    |
    */

    'duplicate_detection' => [
        'similarity_threshold' => 0.6, // Minimum score to flag as potential duplicate
        'auto_scan_enabled' => true,   // Run background duplicate scans
        'scan_frequency' => 'daily',   // How often to scan: daily, weekly
    ],

    /*
    |--------------------------------------------------------------------------
    | Check-In Settings (Phase 2)
    |--------------------------------------------------------------------------
    |
    | Settings for wellness check-ins
    |
    */

    'check_in' => [
        'enabled' => false, // Will be enabled in Phase 2
        'reminder_frequency' => 'weekly',
        'missed_threshold_hours' => 72,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Photo storage settings
    |
    */

    'storage' => [
        'disk' => env('UBUMI_STORAGE_DISK', 'public'),
        'path' => 'ubumi/photos',
    ],

];
