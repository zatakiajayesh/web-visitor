# Installation Guide

Complete step-by-step guide to set up Web Visitor Tracker.

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache 2.4+ with mod_rewrite (or Nginx)
- Composer (optional, for dependency management)
- 50MB+ disk space

## Installation Steps

### Step 1: Clone Repository

```bash
git clone https://github.com/zatakiajayesh/web-visitor.git
cd web-visitor
```

### Step 2: Configure Environment

Copy the example environment file and update it with your database credentials:

```bash
cp .env.example .env
```

Edit `.env`:

```ini
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=web_visitor
DB_PORT=3306

APP_DEBUG=true
APP_ENV=development
```

### Step 3: Set Permissions

Create necessary directories and set permissions:

```bash
mkdir -p logs cache sessions uploads
chmod 755 logs cache sessions uploads
```

### Step 4: Create Database

#### Option A: Using SQL File

```bash
mysql -u root -p < database/schema.sql
```

#### Option B: Using PHP Setup Script

```bash
php database/setup.php
```

#### Option C: Manual Database Creation

1. Create database:
```sql
CREATE DATABASE web_visitor DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE web_visitor;
```

2. Run schema.sql file in MySQL admin panel

### Step 5: Configure Web Server

#### Apache Configuration

Create `.htaccess` in public directory or use virtual host:

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /path/to/web-visitor/public
    
    <Directory /path/to/web-visitor/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable mod_rewrite:
```bash
a2enmod rewrite
systemctl restart apache2
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/web-visitor/public;
    index index.html;
    
    location / {
        try_files $uri $uri/ =404;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Step 6: Verify Installation

1. Open browser and navigate to: `http://localhost/web-visitor/admin`
2. Login with default credentials:
   - Email: `admin@example.com`
   - Password: `admin123`

## Post-Installation

### Change Default Admin Password

1. Login with default credentials
2. Go to Settings → Change Password
3. Update your password

### Add Your Domain to Tracking

1. Add this script to your website:

```html
<script src="http://localhost/web-visitor/tracker.js"></script>
```

2. Start tracking visits!

### Optimize Database

Run these commands to optimize tables:

```sql
USE web_visitor;
OPTIMIZE TABLE users;
OPTIMIZE TABLE visitors;
OPTIMIZE TABLE page_visits;
OPTIMIZE TABLE analytics;
```

## Troubleshooting

### Database Connection Error

**Error:** "SQLSTATE[HY000]: General error: 2006 MySQL server has gone away"

**Solution:**
- Check MySQL is running
- Verify database credentials in `.env`
- Increase max_connections in MySQL config

### Permission Denied Errors

**Error:** "Warning: fopen(logs/app.log): Permission denied"

**Solution:**
```bash
sudo chown -R www-data:www-data /path/to/web-visitor
chmod -R 755 logs/ cache/ sessions/ uploads/
```

### Module Not Enabled

**Error:** "404 Not Found" for all API routes

**Solution:**
- For Apache: Enable mod_rewrite
- Check `.htaccess` is present in public directory
- Verify AllowOverride is set to All in VirtualHost

### PHP Version Issues

**Error:** "Parse error: syntax error, unexpected '('" 

**Solution:**
- Verify PHP 7.4 or higher is installed
- Update PHP version if running PHP 7.3 or lower

### Blank Admin Dashboard

**Error:** Admin loads but no data shows

**Solution:**
- Check browser console for JavaScript errors
- Verify API endpoints are accessible
- Check CORS headers if using separate domain

## Security Setup

### Change Application Key

Generate new encryption key in `.env`:

```bash
php -r 'echo bin2hex(random_bytes(32));'
```

Update `APP_KEY` in `.env` file.

### Enable HTTPS

1. Install SSL certificate
2. Update `APP_URL` in `.env`:
```
APP_URL=https://yourdomain.com/web-visitor
```

3. Set in `.env`:
```
COOKIE_SECURE=true
```

### Database Backups

Set up regular backups:

```bash
# Daily backup script
mysqldump -u root -p web_visitor > backups/backup_$(date +%Y%m%d).sql
```

## Performance Optimization

### Enable Caching

Update `.env`:
```
CACHE_DRIVER=file
CACHE_TTL=3600
```

### Database Indexing

Indexes are created by schema. Monitor query performance:

```sql
EXPLAIN SELECT * FROM page_visits WHERE visitor_id = 1;
```

### Log Rotation

Configure log rotation to prevent disk space issues:

```bash
# /etc/logrotate.d/web-visitor
/path/to/web-visitor/logs/*.log {
    daily
    rotate 7
    compress
    delaycompress
    notifempty
}
```

## Updating

To update to latest version:

```bash
cd /path/to/web-visitor
git pull origin main
php database/setup.php
```

## Getting Help

- Check `README.md` for general information
- Check `docs/API.md` for API documentation
- Review logs in `logs/` directory
- Create issue on GitHub

## Next Steps

1. Configure your domain
2. Add tracking script to your website
3. Customize dashboard styling
4. Set up analytics reports
5. Configure backup strategy
