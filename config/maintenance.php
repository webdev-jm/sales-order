<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maintenance mode from the environment
    |--------------------------------------------------------------------------
    |
    | Set MAINTENANCE_MODE=true in .env to show the maintenance page to
    | everyone, then refresh the config cache (php artisan config:cache).
    | `php artisan down` shows the same page.
    |
    | MAINTENANCE_SECRET lets the team in while it is on: open
    | /?maintenance=<secret> once and the browser keeps a pass cookie.
    | MAINTENANCE_ALLOWED_IPS is a comma-separated list of addresses that are
    | never stopped. MAINTENANCE_UNTIL (e.g. "2026-11-24 18:00") shows when it
    | is expected back, and MAINTENANCE_MESSAGE replaces the usual words.
    |
    | To give people a warning, set MAINTENANCE_STARTS_AT instead of turning
    | MAINTENANCE_MODE on (e.g. "2026-11-24 17:00"). For the
    | MAINTENANCE_WARNING_MINUTES before it (30 unless set), every page counts
    | down to it, and at that time the maintenance page comes on by itself.
    | Either way it stays on until the setting is taken out again.
    |
    */

    'enabled' => (bool) env('MAINTENANCE_MODE', false),

    'starts_at' => env('MAINTENANCE_STARTS_AT'),

    'warning_minutes' => (int) env('MAINTENANCE_WARNING_MINUTES', 30),

    'message' => env('MAINTENANCE_MESSAGE'),

    'until' => env('MAINTENANCE_UNTIL'),

    'secret' => env('MAINTENANCE_SECRET'),

    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('MAINTENANCE_ALLOWED_IPS', ''))))),

    // Seconds a browser or crawler is told to wait before trying again.
    'retry_after' => 300,

    'cookie' => 'sms_maintenance_pass',

];
