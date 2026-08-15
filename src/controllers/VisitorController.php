<?php
/**
 * Visitor Tracking Controller
 * Handles visitor tracking API endpoints
 */

class VisitorController {
    private $visitor;
    
    public function __construct() {
        $this->visitor = new Visitor();
    }
    
    /**
     * Track visitor visit
     */
    public function trackVisit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return ['error' => 'Method not allowed'];
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $pageUrl = $data['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        $referrer = $data['referrer'] ?? null;
        
        if (empty($pageUrl)) {
            http_response_code(400);
            return ['error' => 'Page URL required'];
        }
        
        try {
            $visitorId = $this->visitor->trackVisitor($ipAddress, $userAgent, $pageUrl, $referrer);
            
            return [
                'success' => true,
                'visitor_id' => $visitorId,
                'message' => 'Visit tracked successfully'
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to track visit: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update visit duration
     */
    public function updateDuration() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return ['error' => 'Method not allowed'];
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['page_visit_id']) || !isset($data['duration'])) {
            http_response_code(400);
            return ['error' => 'Page visit ID and duration required'];
        }
        
        $scrollDepth = $data['scroll_depth'] ?? 0;
        
        if ($this->visitor->updateVisitDuration($data['page_visit_id'], $data['duration'], $scrollDepth)) {
            return ['success' => true, 'message' => 'Duration updated'];
        }
        
        http_response_code(500);
        return ['error' => 'Failed to update duration'];
    }
    
    /**
     * Get visitor information
     */
    public function getVisitor($visitorId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            return ['error' => 'Method not allowed'];
        }
        
        $visitor = $this->visitor->getVisitor($visitorId);
        
        if (!$visitor) {
            http_response_code(404);
            return ['error' => 'Visitor not found'];
        }
        
        return ['success' => true, 'visitor' => $visitor];
    }
    
    /**
     * Get all visitors
     */
    public function getAllVisitors() {
        session_start();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            return ['error' => 'Unauthorized'];
        }
        
        $limit = $_GET['limit'] ?? 50;
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * $limit;
        
        $visitors = $this->visitor->getAllVisitors($limit, $offset);
        
        return [
            'success' => true,
            'visitors' => $visitors,
            'limit' => $limit,
            'page' => $page
        ];
    }
    
    /**
     * Get visitor statistics
     */
    public function getStatistics() {
        session_start();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            return ['error' => 'Unauthorized'];
        }
        
        $stats = $this->visitor->getVisitorStats();
        
        return ['success' => true, 'stats' => $stats];
    }
    
    /**
     * Get active visitors
     */
    public function getActiveVisitors() {
        session_start();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            return ['error' => 'Unauthorized'];
        }
        
        $minutes = $_GET['minutes'] ?? 30;
        $activeVisitors = $this->visitor->getActiveVisitors($minutes);
        
        return [
            'success' => true,
            'active_visitors' => $activeVisitors,
            'count' => count($activeVisitors)
        ];
    }
    
    /**
     * Get visitor page history
     */
    public function getPageHistory($visitorId) {
        session_start();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            return ['error' => 'Unauthorized'];
        }
        
        $limit = $_GET['limit'] ?? 50;
        $history = $this->visitor->getVisitorPageHistory($visitorId, $limit);
        
        return [
            'success' => true,
            'history' => $history
        ];
    }
}
