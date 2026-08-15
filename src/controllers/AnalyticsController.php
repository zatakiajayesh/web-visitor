<?php
/**
 * Analytics Controller
 * Handles analytics and reporting endpoints
 */

class AnalyticsController {
    private $analytics;
    
    public function __construct() {
        $this->analytics = new Analytics();
    }
    
    /**
     * Require admin authentication
     */
    private function requireAdmin() {
        session_start();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            die(json_encode(['error' => 'Unauthorized']));
        }
    }
    
    /**
     * Get analytics summary
     */
    public function getSummary() {
        $this->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $summary = $this->analytics->getSummary($startDate, $endDate);
        
        return [
            'success' => true,
            'summary' => $summary
        ];
    }
    
    /**
     * Get today's analytics
     */
    public function getToday() {
        $this->requireAdmin();
        
        $today = $this->analytics->getTodayAnalytics();
        
        return [
            'success' => true,
            'today' => $today
        ];
    }
    
    /**
     * Get top pages
     */
    public function getTopPages() {
        $this->requireAdmin();
        
        $limit = $_GET['limit'] ?? 10;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $topPages = $this->analytics->getTopPages($limit, $startDate, $endDate);
        
        return [
            'success' => true,
            'pages' => $topPages
        ];
    }
    
    /**
     * Get top referrers
     */
    public function getTopReferrers() {
        $this->requireAdmin();
        
        $limit = $_GET['limit'] ?? 10;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $referrers = $this->analytics->getTopReferrers($limit, $startDate, $endDate);
        
        return [
            'success' => true,
            'referrers' => $referrers
        ];
    }
    
    /**
     * Get browser statistics
     */
    public function getBrowserStats() {
        $this->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $stats = $this->analytics->getBrowserStats($startDate, $endDate);
        
        return [
            'success' => true,
            'browsers' => $stats
        ];
    }
    
    /**
     * Get device statistics
     */
    public function getDeviceStats() {
        $this->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $stats = $this->analytics->getDeviceStats($startDate, $endDate);
        
        return [
            'success' => true,
            'devices' => $stats
        ];
    }
    
    /**
     * Get OS statistics
     */
    public function getOSStats() {
        $this->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $stats = $this->analytics->getOSStats($startDate, $endDate);
        
        return [
            'success' => true,
            'os' => $stats
        ];
    }
    
    /**
     * Get hourly analytics
     */
    public function getHourlyAnalytics() {
        $this->requireAdmin();
        
        $date = $_GET['date'] ?? null;
        
        $hourly = $this->analytics->getHourlyAnalytics($date);
        
        return [
            'success' => true,
            'hourly' => $hourly
        ];
    }
    
    /**
     * Get bounce rate
     */
    public function getBounceRate() {
        $this->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $bounce = $this->analytics->getBounceRate($startDate, $endDate);
        
        return [
            'success' => true,
            'bounce_rate' => $bounce
        ];
    }
    
    /**
     * Generate full report
     */
    public function generateReport() {
        $this->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $report = [
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'summary' => $this->analytics->getSummary($startDate, $endDate),
            'top_pages' => $this->analytics->getTopPages(10, $startDate, $endDate),
            'top_referrers' => $this->analytics->getTopReferrers(10, $startDate, $endDate),
            'browser_stats' => $this->analytics->getBrowserStats($startDate, $endDate),
            'device_stats' => $this->analytics->getDeviceStats($startDate, $endDate),
            'bounce_rate' => $this->analytics->getBounceRate($startDate, $endDate),
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        return [
            'success' => true,
            'report' => $report
        ];
    }
}
