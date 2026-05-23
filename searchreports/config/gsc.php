<?php

return [
    'cache_hours' => (int) env('GSC_CACHE_HOURS', 6),
    'row_limit' => 25000,
    'max_retries' => 3,
    'retry_delay_ms' => 1000,
    'scopes' => [
        'openid',
        'email',
        'profile',
        'https://www.googleapis.com/auth/webmasters.readonly',
    ],
    'reports_disk' => 'local',
    'reports_path' => 'reports',
    'report_expires_days' => 30,
];
