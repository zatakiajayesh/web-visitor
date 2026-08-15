<?php
/**
 * Session Configuration
 */

return [
    // Session driver
    'driver' => 'file',
    
    // Session timeout in seconds (1 hour)
    'lifetime' => getenv('SESSION_TIMEOUT') ?: 3600,
    
    // Session cookie name
    'cookie' => 'PHPSESSID',
    
    // Cookie secure flag
    'secure' => getenv('COOKIE_SECURE') == 'true',
    
    // Cookie HTTP only flag
    'http_only' => getenv('COOKIE_HTTPONLY') == 'true',
    
    // Cookie path
    'path' => '/',
    
    // Cookie domain
    'domain' => null,
    
    // Session path
    'path' => __DIR__ . '/../sessions/',
];
