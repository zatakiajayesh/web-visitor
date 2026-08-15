<?php
/**
 * Analytics Model Class
 * Handles analytics calculations and reporting
 */

class Analytics {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get analytics summary for a date range
     */
    public function getSummary($startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-30 days'));
        if (!$endDate) $endDate = date('Y-m-d');
        
        $this->db->query("
            SELECT 
                COUNT(DISTINCT pv.visitor_id) as unique_visitors,
                COUNT(pv.id) as page_views,
                COUNT(DISTINCT v.id) as total_visitors,
                AVG(pv.duration) as avg_session_duration,
                MIN(pv.visited_at) as first_visit,
                MAX(pv.visited_at) as last_visit
            FROM page_visits pv
            JOIN visitors v ON pv.visitor_id = v.id
            WHERE DATE(pv.visited_at) BETWEEN :start AND :end
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->single();
    }
    
    /**
     * Get today's analytics
     */
    public function getTodayAnalytics() {
        $this->db->query("
            SELECT 
                COUNT(DISTINCT pv.visitor_id) as unique_visitors,
                COUNT(pv.id) as page_views,
                AVG(pv.duration) as avg_session_duration,
                COUNT(DISTINCT HOUR(pv.visited_at)) as active_hours
            FROM page_visits pv
            WHERE DATE(pv.visited_at) = CURDATE()
        ");
        return $this->db->single();
    }
    
    /**
     * Get top pages
     */
    public function getTopPages($limit = 10, $startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-7 days'));
        if (!$endDate) $endDate = date('Y-m-d');
        
        $this->db->query("
            SELECT 
                page_url,
                COUNT(*) as visits,
                COUNT(DISTINCT visitor_id) as unique_visitors,
                AVG(duration) as avg_duration
            FROM page_visits
            WHERE DATE(visited_at) BETWEEN :start AND :end
            GROUP BY page_url
            ORDER BY visits DESC
            LIMIT :limit
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    
    /**
     * Get top referrers
     */
    public function getTopReferrers($limit = 10, $startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-7 days'));
        if (!$endDate) $endDate = date('Y-m-d');
        
        $this->db->query("
            SELECT 
                referrer,
                COUNT(*) as visits,
                COUNT(DISTINCT visitor_id) as unique_visitors
            FROM page_visits
            WHERE DATE(visited_at) BETWEEN :start AND :end AND referrer IS NOT NULL
            GROUP BY referrer
            ORDER BY visits DESC
            LIMIT :limit
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    
    /**
     * Get browser statistics
     */
    public function getBrowserStats($startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-7 days'));
        if (!$endDate) $endDate = date('Y-m-d');
        
        $this->db->query("
            SELECT 
                v.browser,
                COUNT(DISTINCT v.id) as unique_visitors,
                COUNT(pv.id) as page_views
            FROM visitors v
            LEFT JOIN page_visits pv ON v.id = pv.visitor_id
            WHERE DATE(pv.visited_at) BETWEEN :start AND :end
            GROUP BY v.browser
            ORDER BY unique_visitors DESC
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->resultSet();
    }
    
    /**
     * Get device statistics
     */
    public function getDeviceStats($startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-7 days'));
        if (!$endDate) $endDate = date('Y-m-d');
        
        $this->db->query("
            SELECT 
                v.device_type,
                COUNT(DISTINCT v.id) as unique_visitors,
                COUNT(pv.id) as page_views,
                AVG(pv.duration) as avg_duration
            FROM visitors v
            LEFT JOIN page_visits pv ON v.id = pv.visitor_id
            WHERE DATE(pv.visited_at) BETWEEN :start AND :end
            GROUP BY v.device_type
            ORDER BY unique_visitors DESC
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->resultSet();
    }
    
    /**
     * Get OS statistics
     */
    public function getOSStats($startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-7 days'));
        if (!$endDate) $endDate = date('Y-m-d');
        
        $this->db->query("
            SELECT 
                v.os,
                COUNT(DISTINCT v.id) as unique_visitors,
                COUNT(pv.id) as page_views
            FROM visitors v
            LEFT JOIN page_visits pv ON v.id = pv.visitor_id
            WHERE DATE(pv.visited_at) BETWEEN :start AND :end
            GROUP BY v.os
            ORDER BY unique_visitors DESC
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->resultSet();
    }
    
    /**
     * Get hourly analytics
     */
    public function getHourlyAnalytics($date = null) {
        if (!$date) $date = date('Y-m-d');
        
        $this->db->query("
            SELECT 
                HOUR(visited_at) as hour,
                COUNT(DISTINCT visitor_id) as unique_visitors,
                COUNT(*) as page_views,
                AVG(duration) as avg_duration
            FROM page_visits
            WHERE DATE(visited_at) = :date
            GROUP BY HOUR(visited_at)
            ORDER BY hour ASC
        ");
        $this->db->bind(':date', $date);
        return $this->db->resultSet();
    }
    
    /**
     * Get bounce rate
     */
    public function getBounceRate($startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-7 days'));
        if (!$endDate) $endDate = date('Y-m-d');
        
        $this->db->query("
            SELECT 
                COUNT(DISTINCT CASE WHEN visit_count = 1 THEN id END) as bounced_visitors,
                COUNT(DISTINCT id) as total_visitors,
                ROUND(
                    (COUNT(DISTINCT CASE WHEN visit_count = 1 THEN id END) / 
                     COUNT(DISTINCT id)) * 100, 2
                ) as bounce_rate
            FROM visitors
            WHERE DATE(first_visit) BETWEEN :start AND :end
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->single();
    }
    
    /**
     * Save analytics snapshot to database
     */
    public function saveSnapshot($date = null) {
        if (!$date) $date = date('Y-m-d');
        
        $summary = $this->getSummary($date, $date);
        $topPages = $this->getTopPages(1, $date, $date);
        $topReferrers = $this->getTopReferrers(1, $date, $date);
        
        $this->db->query("
            INSERT INTO analytics 
            (date, total_visitors, page_views, bounce_rate, top_page, top_referrer)
            VALUES (:date, :total_visitors, :page_views, :bounce_rate, :top_page, :top_referrer)
            ON DUPLICATE KEY UPDATE
            total_visitors = :total_visitors,
            page_views = :page_views,
            bounce_rate = :bounce_rate,
            top_page = :top_page,
            top_referrer = :top_referrer
        ");
        $this->db->bind(':date', $date);
        $this->db->bind(':total_visitors', $summary['total_visitors'] ?? 0, PDO::PARAM_INT);
        $this->db->bind(':page_views', $summary['page_views'] ?? 0, PDO::PARAM_INT);
        $this->db->bind(':bounce_rate', 0);
        $this->db->bind(':top_page', $topPages[0]['page_url'] ?? null);
        $this->db->bind(':top_referrer', $topReferrers[0]['referrer'] ?? null);
        
        return $this->db->execute();
    }
}
