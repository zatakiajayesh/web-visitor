# Web Visitor Tracking Application

A comprehensive PHP MySQL application for tracking website visitors with advanced analytics, user authentication, and admin dashboard.

## Features

- 🔐 **User Authentication** - Secure login/registration system
- 📊 **Visitor Tracking** - Real-time visitor logging and session management
- 📈 **Analytics Dashboard** - Comprehensive analytics and reporting
- 🗂️ **Admin Panel** - Manage users, view statistics, configure settings
- ⚡ **Performance Optimized** - Database optimization, caching, and indexing
- 📱 **Responsive Design** - Mobile-friendly interface
- 🔒 **Security** - Password hashing, CSRF protection, SQL injection prevention

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Apache/Nginx with mod_rewrite enabled

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/zatakiajayesh/web-visitor.git
cd web-visitor
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Database

Copy `.env.example` to `.env` and update your database credentials:

```bash
cp .env.example .env
```

### 4. Create Database and Tables

```bash
mysql -u root -p < database/schema.sql
```

### 5. Set Permissions

```bash
chmod 755 logs/
chmod 755 cache/
chmod 755 uploads/
chmod 755 sessions/
```

### 6. Access Application

- **Frontend**: `http://localhost/web-visitor`
- **Admin Dashboard**: `http://localhost/web-visitor/admin`

## Project Structure

```
web-visitor/
├── config/              # Configuration files
├── public/              # Public web root
├── src/
│   ├── classes/         # Core classes
│   ├── controllers/     # Request controllers
│   ├── models/          # Database models
│   └── utils/           # Utility functions
├── views/               # View templates
├── database/            # Database setup and migrations
├── assets/
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Images
├── logs/                # Application logs
├── cache/               # Cache files
└── uploads/             # User uploads
```

## License

MIT License - see LICENSE file for details
