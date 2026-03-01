<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Control feature rollout and A/B testing.
    | Set to true to enable, false to disable.
    |
    */

    'financial' => [
        /*
        | Enable transaction source tracking
        | 
        | When enabled, new transactions will include module source
        */
        'track_transaction_source' => env('FEATURE_TRACK_SOURCE', true),
    ],
];
