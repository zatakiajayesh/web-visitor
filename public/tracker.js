/**
 * Web Visitor Tracking Script
 * Add this script to your website to track visitors
 */

(function() {
    'use strict';
    
    const TRACKER_URL = '/web-visitor/api/track/visit';
    const API_BASE = '/web-visitor/api';
    
    /**
     * Initialize visitor tracking
     */
    function initTracker() {
        const startTime = Date.now();
        let lastScrollDepth = 0;
        
        // Track page visit
        trackPageVisit();
        
        // Track scroll depth
        window.addEventListener('scroll', function() {
            lastScrollDepth = Math.round((window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100);
        });
        
        // Update duration before leaving
        window.addEventListener('beforeunload', function() {
            const duration = Math.floor((Date.now() - startTime) / 1000);
            updateVisitDuration(duration, lastScrollDepth);
        });
    }
    
    /**
     * Track page visit
     */
    function trackPageVisit() {
        const data = {
            page_url: window.location.href,
            referrer: document.referrer,
            user_agent: navigator.userAgent
        };
        
        fetch(TRACKER_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store visitor ID in session storage
                sessionStorage.setItem('visitor_id', data.visitor_id);
            }
        })
        .catch(error => console.error('Tracking error:', error));
    }
    
    /**
     * Update visit duration
     */
    function updateVisitDuration(duration, scrollDepth) {
        const visitorId = sessionStorage.getItem('visitor_id');
        if (!visitorId) return;
        
        const data = {
            visitor_id: visitorId,
            duration: duration,
            scroll_depth: scrollDepth
        };
        
        navigator.sendBeacon(API_BASE + '/track/duration', JSON.stringify(data));
    }
    
    /**
     * Get analytics data
     */
    window.getAnalytics = function(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = API_BASE + '/analytics/' + endpoint + (queryString ? '?' + queryString : '');
        
        return fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json());
    };
    
    /**
     * Authenticate user
     */
    window.authenticate = function(email, password) {
        return fetch(API_BASE + '/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ email, password })
        })
        .then(response => response.json());
    };
    
    /**
     * Logout user
     */
    window.logout = function() {
        return fetch(API_BASE + '/auth/logout', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json());
    };
    
    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTracker);
    } else {
        initTracker();
    }
})();
