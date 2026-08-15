<?php
/**
 * Logging Configuration
 */

return [
    // Default log channel
    'default' => 'single',
    
    // Log level
    'level' => getenv('LOG_LEVEL') ?: 'info',
    
    // Log channels
    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => getenv('LOG_PATH') ?: __DIR__ . '/../logs/app.log',
            'level' => 'debug',
        ],
        'daily' => [
            'driver' => 'daily',
            'path' => getenv('LOG_PATH') ?: __DIR__ . '/../logs/app.log',
            'level' => 'debug',
            'days' => 14,
        ],
        'error' => [
            'driver' => 'single',
            'path' => getenv('LOG_PATH') ?: __DIR__ . '/../logs/error.log',
            'level' => 'error',
        ],
    ],
];
