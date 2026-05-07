<?php

return [
    'enabled' => env('QUERYLENS_ENABLED', true),

    // Highlight queries slower than this (in milliseconds)
    'slow_query_threshold' => 500,

    // How many requests to keep in the dashboard
    'max_requests' => 50,

    // Routes to ignore
    'except' => [
        'querylens*',
        'telescope*',
    ],
];
