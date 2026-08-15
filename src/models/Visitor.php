<?php
/**
 * Visitor Model Class
 * Handles visitor tracking and data operations
 */

class Visitor {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Track a new visitor or update existing
     */
    public function trackVisitor($ipAddress, $userAgent, $pageUrl, $referrer = null) {
        $visitorToken = $this->generateVisitorToken($ipAddress, $userAgent);
        
        // Check if visitor already exists
        $this->db->query("SELECT id FROM visitors WHERE visitor_token = :token");
        $this->db->bind(':token', $visitorToken);
        $existingVisitor = $this->db->single();
        
        if ($existingVisitor) {
            $visitorId = $existingVisitor['id'];
            // Update last visit
            $this->db->query("UPDATE visitors SET last_visit = NOW(), visit_count = visit_count + 1 WHERE id = :id");
            $this->db->bind(':id', $visitorId, PDO::PARAM_INT);
            $this->db->execute();
        } else {
            // Create new visitor
            $this->db->query("
                INSERT INTO visitors (visitor_token, ip_address, user_agent, browser, device_type, os)
                VALUES (:token, :ip, :ua, :browser, :device, :os)
            ");
            $this->db->bind(':token', $visitorToken);
            $this->db->bind(':ip', $ipAddress);
            $this->db->bind(':ua', $userAgent);
            $this->db->bind(':browser', $this->parseBrowser($userAgent));
            $this->db->bind(':device', $this->parseDeviceType($userAgent));
            $this->db->bind(':os', $this->parseOS($userAgent));
            
            $this->db->execute();
            $visitorId = $this->db->lastInsertId();
        }
        
        // Record page visit
        $this->recordPageVisit($visitorId, $pageUrl, $referrer);
        
        return $visitorId;
    }
    
    /**
     * Record a page visit
     */
    public function recordPageVisit($visitorId, $pageUrl, $referrer = null) {
        $this->db->query("
            INSERT INTO page_visits (visitor_id, page_url, referrer, title)
            VALUES (:visitor_id, :url, :referrer, :title)
        ");
        $this->db->bind(':visitor_id', $visitorId, PDO::PARAM_INT);
        $this->db->bind(':url', $pageUrl);
        $this->db->bind(':referrer', $referrer);
        $this->db->bind(':title', $this->extractPageTitle($pageUrl));
        
        return $this->db->execute();
    }
    
    /**
     * Update page visit duration
     */
    public function updateVisitDuration($pageVisitId, $duration, $scrollDepth = 0) {
        $this->db->query("
            UPDATE page_visits 
            SET duration = :duration, scroll_depth = :scroll
            WHERE id = :id
        ");
        $this->db->bind(':duration', $duration, PDO::PARAM_INT);
        $this->db->bind(':scroll', $scrollDepth, PDO::PARAM_INT);
        $this->db->bind(':id', $pageVisitId, PDO::PARAM_INT);
        
        return $this->db->execute();
    }
    
    /**
     * Get visitor information
     */
    public function getVisitor($visitorId) {
        $this->db->query("SELECT * FROM visitors WHERE id = :id");
        $this->db->bind(':id', $visitorId, PDO::PARAM_INT);
        return $this->db->single();
    }
    
    /**
     * Get all visitors with pagination
     */
    public function getAllVisitors($limit = 50, $offset = 0) {
        $this->db->query("
            SELECT id, visitor_token, ip_address, country, device_type, browser, 
                   visit_count, first_visit, last_visit
            FROM visitors
            ORDER BY last_visit DESC
            LIMIT :limit OFFSET :offset
        ");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    
    /**
     * Get visitor statistics
     */
    public function getVisitorStats() {
        $this->db->query("
            SELECT 
                COUNT(DISTINCT id) as total_visitors,
                SUM(visit_count) as total_visits,
                AVG(visit_count) as avg_visits_per_visitor,
                COUNT(CASE WHEN is_new = 1 THEN 1 END) as new_visitors
            FROM visitors
        ");
        return $this->db->single();
    }
    
    /**
     * Get today's active visitors
     */
    public function getActiveVisitors($minutes = 30) {
        $this->db->query("
            SELECT DISTINCT v.id, v.visitor_token, v.ip_address, v.browser, v.device_type
            FROM visitors v
            JOIN page_visits pv ON v.id = pv.visitor_id
            WHERE pv.visited_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
            ORDER BY pv.visited_at DESC
        ");
        $this->db->bind(':minutes', $minutes, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    
    /**
     * Get page visits for a visitor
     */
    public function getVisitorPageHistory($visitorId, $limit = 50) {
        $this->db->query("
            SELECT page_url, title, referrer, duration, visited_at
            FROM page_visits
            WHERE visitor_id = :visitor_id
            ORDER BY visited_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':visitor_id', $visitorId, PDO::PARAM_INT);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    
    /**
     * Generate unique visitor token
     */
    private function generateVisitorToken($ipAddress, $userAgent) {
        return hash('sha256', $ipAddress . $userAgent);
    }
    
    /**
     * Parse browser from user agent
     */
    private function parseBrowser($userAgent) {
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        return 'Other';
    }
    
    /**
     * Parse device type from user agent
     */
    private function parseDeviceType($userAgent) {
        if (strpos($userAgent, 'Mobile') !== false) return 'Mobile';
        if (strpos($userAgent, 'Tablet') !== false) return 'Tablet';
        return 'Desktop';
    }
    
    /**
     * Parse OS from user agent
     */
    private function parseOS($userAgent) {
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'macOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'iPhone') !== false) return 'iOS';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        return 'Other';
    }
    
    /**
     * Extract page title from URL
     */
    private function extractPageTitle($pageUrl) {
        $path = parse_url($pageUrl, PHP_URL_PATH);
        $title = basename($path);
        return !empty($title) && $title !== '/' ? $title : 'Home';
    }
}
