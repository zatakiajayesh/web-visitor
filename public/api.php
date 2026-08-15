<?php
/**
 * API Router
 * Routes API requests to appropriate controllers
 */

require_once __DIR__ . '/../src/classes/Database.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/models/Visitor.php';
require_once __DIR__ . '/../src/models/Analytics.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/VisitorController.php';
require_once __DIR__ . '/../src/controllers/AnalyticsController.php';

// Enable error reporting in development
if (getenv('APP_DEBUG')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set JSON response header
header('Content-Type: application/json');

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/web-visitor/api', '', $path);

// Parse route
$routeParts = array_filter(explode('/', $path));
$routeParts = array_values($routeParts);

$response = null;

try {
    // Route requests
    if (empty($routeParts)) {
        http_response_code(404);
        $response = ['error' => 'Not found'];
    } 
    // Authentication endpoints
    elseif ($routeParts[0] === 'auth') {
        $authController = new AuthController();
        
        if ($routeParts[1] === 'login') {
            $response = $authController->login();
        } elseif ($routeParts[1] === 'register') {
            $response = $authController->register();
        } elseif ($routeParts[1] === 'logout') {
            $response = $authController->logout();
        } elseif ($routeParts[1] === 'user') {
            $response = $authController->getCurrentUser();
        } elseif ($routeParts[1] === 'update-profile') {
            $response = $authController->updateProfile();
        } elseif ($routeParts[1] === 'change-password') {
            $response = $authController->changePassword();
        } else {
            http_response_code(404);
            $response = ['error' => 'Endpoint not found'];
        }
    }
    // Visitor tracking endpoints
    elseif ($routeParts[0] === 'track') {
        $visitorController = new VisitorController();
        
        if ($routeParts[1] === 'visit') {
            $response = $visitorController->trackVisit();
        } elseif ($routeParts[1] === 'duration') {
            $response = $visitorController->updateDuration();
        } else {
            http_response_code(404);
            $response = ['error' => 'Endpoint not found'];
        }
    }
    // Stats endpoints
    elseif ($routeParts[0] === 'stats') {
        $visitorController = new VisitorController();
        
        if ($routeParts[1] === 'summary') {
            $response = $visitorController->getVisitorStats();
        } elseif ($routeParts[1] === 'visitors') {
            $response = $visitorController->getAllVisitors();
        } elseif ($routeParts[1] === 'active') {
            $response = $visitorController->getActiveVisitors();
        } elseif ($routeParts[1] === 'history' && isset($routeParts[2])) {
            $response = $visitorController->getPageHistory($routeParts[2]);
        } else {
            http_response_code(404);
            $response = ['error' => 'Endpoint not found'];
        }
    }
    // Analytics endpoints
    elseif ($routeParts[0] === 'analytics') {
        $analyticsController = new AnalyticsController();
        
        if ($routeParts[1] === 'summary') {
            $response = $analyticsController->getSummary();
        } elseif ($routeParts[1] === 'today') {
            $response = $analyticsController->getToday();
        } elseif ($routeParts[1] === 'top-pages') {
            $response = $analyticsController->getTopPages();
        } elseif ($routeParts[1] === 'top-referrers') {
            $response = $analyticsController->getTopReferrers();
        } elseif ($routeParts[1] === 'browsers') {
            $response = $analyticsController->getBrowserStats();
        } elseif ($routeParts[1] === 'devices') {
            $response = $analyticsController->getDeviceStats();
        } elseif ($routeParts[1] === 'os') {
            $response = $analyticsController->getOSStats();
        } elseif ($routeParts[1] === 'hourly') {
            $response = $analyticsController->getHourlyAnalytics();
        } elseif ($routeParts[1] === 'bounce-rate') {
            $response = $analyticsController->getBounceRate();
        } elseif ($routeParts[1] === 'report') {
            $response = $analyticsController->generateReport();
        } else {
            http_response_code(404);
            $response = ['error' => 'Endpoint not found'];
        }
    } 
    else {
        http_response_code(404);
        $response = ['error' => 'Not found'];
    }
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => 'Server error: ' . $e->getMessage()];
}

// Return JSON response
echo json_encode($response);
