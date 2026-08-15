/**
 * Admin Dashboard JavaScript
 * Handles all dashboard interactions and API calls
 */

const API_BASE = '/web-visitor/api';
let charts = {};

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

/**
 * Initialize dashboard
 */
function initializeDashboard() {
    // Check authentication
    checkAuth();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load initial data
    loadDashboardData();
}

/**
 * Check user authentication
 */
function checkAuth() {
    fetch(API_BASE + '/auth/user', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('userEmail').textContent = data.user.email;
        } else {
            window.location.href = '/web-visitor/login.html';
        }
    })
    .catch(() => {
        window.location.href = '/web-visitor/login.html';
    });
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Menu items
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;
            switchSection(section);
        });
    });
    
    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', logout);
    
    // Dashboard controls
    document.getElementById('refreshVisitors').addEventListener('click', loadVisitors);
    document.getElementById('filterAnalytics').addEventListener('click', loadAnalytics);
    document.getElementById('generateReport').addEventListener('click', generateReport);
    document.getElementById('downloadReport').addEventListener('click', downloadReport);
}

/**
 * Switch section
 */
function switchSection(section) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    
    // Show selected section
    document.getElementById(section).classList.add('active');
    
    // Update menu
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.section === section) {
            item.classList.add('active');
        }
    });
    
    // Load section data
    switch(section) {
        case 'dashboard':
            loadDashboardData();
            break;
        case 'visitors':
            loadVisitors();
            break;
        case 'analytics':
            loadAnalytics();
            break;
        case 'pages':
            loadTopPages();
            break;
        case 'devices':
            loadDeviceStats();
            break;
        case 'browsers':
            loadBrowserStats();
            break;
        case 'report':
            setDefaultDates();
            break;
    }
}

/**
 * Load dashboard data
 */
function loadDashboardData() {
    Promise.all([
        fetch(API_BASE + '/stats/summary').then(r => r.json()),
        fetch(API_BASE + '/stats/active').then(r => r.json()),
        fetch(API_BASE + '/analytics/today').then(r => r.json()),
        fetch(API_BASE + '/analytics/bounce-rate').then(r => r.json()),
        fetch(API_BASE + '/analytics/hourly').then(r => r.json())
    ])
    .then(([summary, active, today, bounce, hourly]) => {
        // Update stats
        document.getElementById('totalVisitors').textContent = summary.stats.total_visitors || 0;
        document.getElementById('uniqueVisitors').textContent = summary.stats.unique_visitors || 0;
        document.getElementById('pageViews').textContent = summary.stats.page_views || 0;
        document.getElementById('avgDuration').textContent = formatSeconds(summary.stats.avg_session_duration || 0);
        document.getElementById('activeNow').textContent = active.count || 0;
        document.getElementById('bounceRate').textContent = (bounce.bounce_rate?.bounce_rate || 0) + '%';
        
        // Update hourly chart
        if (hourly.hourly && hourly.hourly.length > 0) {
            updateHourlyChart(hourly.hourly);
        }
    })
    .catch(error => console.error('Error loading dashboard:', error));
}

/**
 * Load visitors
 */
function loadVisitors() {
    fetch(API_BASE + '/stats/visitors?limit=50&page=1', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const table = document.getElementById('visitorsTable');
            table.innerHTML = '';
            
            data.visitors.forEach(visitor => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${visitor.ip_address}</td>
                    <td>${visitor.browser}</td>
                    <td>${visitor.device_type}</td>
                    <td>${visitor.visit_count}</td>
                    <td>${new Date(visitor.last_visit).toLocaleString()}</td>
                    <td>
                        <button class="btn-primary" onclick="viewVisitor(${visitor.id})">View</button>
                    </td>
                `;
                table.appendChild(row);
            });
        }
    })
    .catch(error => console.error('Error loading visitors:', error));
}

/**
 * Load analytics
 */
function loadAnalytics() {
    const startDate = document.getElementById('startDate').value || getDefaultStartDate();
    const endDate = document.getElementById('endDate').value || new Date().toISOString().split('T')[0];
    
    fetch(`${API_BASE}/analytics/summary?start_date=${startDate}&end_date=${endDate}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const summary = data.summary;
            const html = `
                <div class="stat-item">
                    <strong>Total Visitors:</strong> ${summary.total_visitors || 0}
                </div>
                <div class="stat-item">
                    <strong>Unique Visitors:</strong> ${summary.unique_visitors || 0}
                </div>
                <div class="stat-item">
                    <strong>Page Views:</strong> ${summary.page_views || 0}
                </div>
                <div class="stat-item">
                    <strong>Avg Session Duration:</strong> ${formatSeconds(summary.avg_session_duration || 0)}
                </div>
                <div class="stat-item">
                    <strong>Period:</strong> ${startDate} to ${endDate}
                </div>
            `;
            document.getElementById('analyticsSummary').innerHTML = html;
        }
    })
    .catch(error => console.error('Error loading analytics:', error));
}

/**
 * Load top pages
 */
function loadTopPages() {
    fetch(API_BASE + '/analytics/top-pages?limit=20', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const table = document.getElementById('pagesTable');
            table.innerHTML = '';
            
            data.pages.forEach(page => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${page.page_url}</td>
                    <td>${page.visits}</td>
                    <td>${page.unique_visitors}</td>
                    <td>${formatSeconds(page.avg_duration || 0)}</td>
                `;
                table.appendChild(row);
            });
        }
    })
    .catch(error => console.error('Error loading pages:', error));
}

/**
 * Load device statistics
 */
function loadDeviceStats() {
    fetch(API_BASE + '/analytics/devices', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update table
            const table = document.getElementById('devicesTable');
            table.innerHTML = '';
            
            data.devices.forEach(device => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${device.device_type}</td>
                    <td>${device.unique_visitors}</td>
                    <td>${device.page_views}</td>
                    <td>${formatSeconds(device.avg_duration || 0)}</td>
                `;
                table.appendChild(row);
            });
            
            // Update chart
            updateDeviceChart(data.devices);
        }
    })
    .catch(error => console.error('Error loading device stats:', error));
}

/**
 * Load browser statistics
 */
function loadBrowserStats() {
    fetch(API_BASE + '/analytics/browsers', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update table
            const table = document.getElementById('browsersTable');
            table.innerHTML = '';
            
            data.browsers.forEach(browser => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${browser.browser}</td>
                    <td>${browser.unique_visitors}</td>
                    <td>${browser.page_views}</td>
                `;
                table.appendChild(row);
            });
            
            // Update chart
            updateBrowserChart(data.browsers);
        }
    })
    .catch(error => console.error('Error loading browser stats:', error));
}

/**
 * Generate report
 */
function generateReport() {
    const startDate = document.getElementById('reportStartDate').value || getDefaultStartDate();
    const endDate = document.getElementById('reportEndDate').value || new Date().toISOString().split('T')[0];
    
    fetch(`${API_BASE}/analytics/report?start_date=${startDate}&end_date=${endDate}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const report = data.report;
            const html = `
                <h3>Report Period: ${report.period.start} to ${report.period.end}</h3>
                <h4>Summary</h4>
                <p>Total Visitors: ${report.summary.total_visitors || 0}</p>
                <p>Unique Visitors: ${report.summary.unique_visitors || 0}</p>
                <p>Page Views: ${report.summary.page_views || 0}</p>
                <p>Bounce Rate: ${(report.bounce_rate?.bounce_rate || 0).toFixed(2)}%</p>
                
                <h4>Top Pages</h4>
                <ul>
                    ${report.top_pages.map(p => `<li>${p.page_url} (${p.visits} visits)</li>`).join('')}
                </ul>
                
                <h4>Top Referrers</h4>
                <ul>
                    ${report.top_referrers.map(r => `<li>${r.referrer || 'Direct'} (${r.visits} visits)</li>`).join('')}
                </ul>
                
                <p class="text-muted">Generated: ${report.generated_at}</p>
            `;
            document.getElementById('reportContent').innerHTML = html;
        }
    })
    .catch(error => console.error('Error generating report:', error));
}

/**
 * Download report as PDF
 */
function downloadReport() {
    alert('PDF download feature coming soon!');
}

/**
 * Update hourly chart
 */
function updateHourlyChart(data) {
    const ctx = document.getElementById('hourlyChart');
    if (!ctx) return;
    
    const hours = data.map(d => d.hour + ':00');
    const visitors = data.map(d => d.unique_visitors);
    const views = data.map(d => d.page_views);
    
    if (charts.hourly) {
        charts.hourly.destroy();
    }
    
    charts.hourly = new Chart(ctx, {
        type: 'line',
        data: {
            labels: hours,
            datasets: [
                {
                    label: 'Unique Visitors',
                    data: visitors,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Page Views',
                    data: views,
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Update device chart
 */
function updateDeviceChart(data) {
    const ctx = document.getElementById('devicesChart');
    if (!ctx) return;
    
    const devices = data.map(d => d.device_type);
    const counts = data.map(d => d.unique_visitors);
    
    if (charts.devices) {
        charts.devices.destroy();
    }
    
    charts.devices = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: devices,
            datasets: [{
                data: counts,
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

/**
 * Update browser chart
 */
function updateBrowserChart(data) {
    const ctx = document.getElementById('browsersChart');
    if (!ctx) return;
    
    const browsers = data.map(d => d.browser);
    const counts = data.map(d => d.unique_visitors);
    
    if (charts.browsers) {
        charts.browsers.destroy();
    }
    
    charts.browsers = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: browsers,
            datasets: [{
                label: 'Unique Visitors',
                data: counts,
                backgroundColor: '#667eea'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y'
        }
    });
}

/**
 * Logout user
 */
function logout() {
    fetch(API_BASE + '/auth/logout', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(() => {
        window.location.href = '/web-visitor/login.html';
    });
}

/**
 * Helper functions
 */
function formatSeconds(seconds) {
    if (seconds < 60) return Math.round(seconds) + 's';
    const minutes = Math.floor(seconds / 60);
    return minutes + 'm';
}

function getDefaultStartDate() {
    const date = new Date();
    date.setDate(date.getDate() - 7);
    return date.toISOString().split('T')[0];
}

function setDefaultDates() {
    document.getElementById('reportStartDate').value = getDefaultStartDate();
    document.getElementById('reportEndDate').value = new Date().toISOString().split('T')[0];
}

function viewVisitor(visitorId) {
    alert('Visitor detail view coming soon!');
}
