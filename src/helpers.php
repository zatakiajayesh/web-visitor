<?php
/**
 * Helper Functions
 * Utility functions used throughout the application
 */

/**
 * Load environment variables from .env file
 */
function loadEnv($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') === false || strpos($line, '#') === 0) {
            continue;
        }
        
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes from value
        if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
            (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
            $value = substr($value, 1, -1);
        }
        
        putenv("$key=$value");
    }
}

/**
 * Get configuration value
 */
function config($key, $default = null) {
    $keys = explode('.', $key);
    $file = array_shift($keys);
    
    $config = require __DIR__ . "/../config/$file.php";
    
    foreach ($keys as $k) {
        $config = $config[$k] ?? null;
        if ($config === null) {
            return $default;
        }
    }
    
    return $config;
}

/**
 * Escape HTML output
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return '0.0.0.0';
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Format duration in seconds to readable time
 */
function formatDuration($seconds) {
    if ($seconds < 60) {
        return $seconds . 's';
    }
    
    $minutes = floor($seconds / 60);
    $secs = $seconds % 60;
    
    if ($minutes < 60) {
        return $minutes . 'm ' . $secs . 's';
    }
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    return $hours . 'h ' . $mins . 'm';
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log activity
 */
function logActivity($userId, $action, $description = '', $status = 'success') {
    $db = new Database();
    $db->query("
        INSERT INTO logs (user_id, action, description, ip_address, status)
        VALUES (:user_id, :action, :description, :ip, :status)
    ");
    $db->bind(':user_id', $userId);
    $db->bind(':action', $action);
    $db->bind(':description', $description);
    $db->bind(':ip', getClientIP());
    $db->bind(':status', $status);
    
    return $db->execute();
}

// Load environment variables on initialization
loadEnv();
