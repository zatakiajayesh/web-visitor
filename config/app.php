<?php
/**
 * Application Configuration
 */

return [
    // Application name
    'name' => getenv('APP_NAME') ?: 'Web Visitor Tracker',
    
    // Application URL
    'url' => getenv('APP_URL') ?: 'http://localhost/web-visitor',
    
    // Debug mode
    'debug' => getenv('APP_DEBUG') == 'true',
    
    // Environment
    'env' => getenv('APP_ENV') ?: 'development',
    
    // Timezone
    'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
    
    // Application key for encryption
    'key' => getenv('APP_KEY'),
];
