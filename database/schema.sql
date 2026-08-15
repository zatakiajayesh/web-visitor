-- Web Visitor Tracking Application Database Schema
-- MySQL 5.7+

-- Create Database
CREATE DATABASE IF NOT EXISTS web_visitor;
USE web_visitor;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') DEFAULT 'user',
  is_active TINYINT(1) DEFAULT 1,
  last_login TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table
CREATE TABLE IF NOT EXISTS sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  session_token VARCHAR(255) NOT NULL UNIQUE,
  ip_address VARCHAR(45),
  user_agent TEXT,
  expires_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_session_token (session_token),
  INDEX idx_user_id (user_id),
  INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Visitors table
CREATE TABLE IF NOT EXISTS visitors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  visitor_token VARCHAR(255) NOT NULL UNIQUE,
  ip_address VARCHAR(45),
  user_agent TEXT,
  country VARCHAR(100),
  city VARCHAR(100),
  browser VARCHAR(100),
  device_type VARCHAR(50),
  os VARCHAR(100),
  first_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  visit_count INT DEFAULT 1,
  is_new TINYINT(1) DEFAULT 1,
  INDEX idx_visitor_token (visitor_token),
  INDEX idx_ip_address (ip_address),
  INDEX idx_first_visit (first_visit),
  INDEX idx_visit_count (visit_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Page Visits table
CREATE TABLE IF NOT EXISTS page_visits (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  visitor_id INT NOT NULL,
  page_url VARCHAR(2048) NOT NULL,
  referrer VARCHAR(2048),
  title VARCHAR(255),
  duration INT DEFAULT 0,
  scroll_depth INT DEFAULT 0,
  visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (visitor_id) REFERENCES visitors(id) ON DELETE CASCADE,
  INDEX idx_visitor_id (visitor_id),
  INDEX idx_visited_at (visited_at),
  INDEX idx_page_url (page_url(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics table (aggregated data for performance)
CREATE TABLE IF NOT EXISTS analytics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE NOT NULL,
  total_visitors INT DEFAULT 0,
  unique_visitors INT DEFAULT 0,
  page_views INT DEFAULT 0,
  sessions_count INT DEFAULT 0,
  bounce_rate DECIMAL(5,2) DEFAULT 0,
  avg_session_duration INT DEFAULT 0,
  top_page VARCHAR(2048),
  top_referrer VARCHAR(2048),
  top_device VARCHAR(100),
  top_browser VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_date (date),
  INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(255) NOT NULL UNIQUE,
  setting_value LONGTEXT,
  data_type VARCHAR(50) DEFAULT 'string',
  description TEXT,
  is_public TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logs table
CREATE TABLE IF NOT EXISTS logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(255) NOT NULL,
  description TEXT,
  ip_address VARCHAR(45),
  status VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_action (action),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create default admin user (password: admin123 - bcrypt hashed)
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE password=password;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, data_type, description, is_public) VALUES
('app_name', 'Web Visitor Tracker', 'string', 'Application Name', 0),
('analytics_enabled', '1', 'boolean', 'Enable Analytics Tracking', 1),
('session_timeout', '3600', 'integer', 'Session Timeout in Seconds', 0),
('max_visitors_per_page', '1000', 'integer', 'Max Visitors to Track per Page', 0),
('cache_enabled', '1', 'boolean', 'Enable Caching', 0)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Create indexes for better query performance
CREATE INDEX idx_visitors_date_range ON page_visits(visited_at DESC);
CREATE INDEX idx_analytics_date_range ON analytics(date DESC);
CREATE INDEX idx_sessions_active ON sessions(user_id, expires_at);

-- View for active visitors
CREATE OR REPLACE VIEW active_visitors AS
SELECT 
  v.id,
  v.visitor_token,
  v.ip_address,
  v.country,
  v.device_type,
  v.browser,
  pv.page_url,
  pv.visited_at
FROM visitors v
LEFT JOIN page_visits pv ON v.id = pv.visitor_id
WHERE pv.visited_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
ORDER BY pv.visited_at DESC;

-- View for today's analytics
CREATE OR REPLACE VIEW today_analytics AS
SELECT 
  COUNT(DISTINCT pv.visitor_id) as unique_visitors,
  COUNT(pv.id) as page_views,
  COUNT(DISTINCT v.id) as total_visitors,
  AVG(pv.duration) as avg_session_duration
FROM page_visits pv
JOIN visitors v ON pv.visitor_id = v.id
WHERE DATE(pv.visited_at) = CURDATE();
