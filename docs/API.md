# API Documentation

## Overview

The Web Visitor Tracker API provides endpoints for visitor tracking, user authentication, and analytics reporting.

## Base URL

```
http://localhost/web-visitor/api
```

## Authentication

Most endpoints require user authentication. Include session cookies with requests.

## Response Format

All responses are JSON.

### Success Response
```json
{
  "success": true,
  "data": {...}
}
```

### Error Response
```json
{
  "error": "Error message"
}
```

## Endpoints

### Authentication

#### Login
```
POST /auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "admin123"
}
```

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

#### Register
```
POST /auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

#### Logout
```
POST /auth/logout
```

#### Get Current User
```
GET /auth/user
```

#### Update Profile
```
POST /auth/update-profile
Content-Type: application/json

{
  "name": "New Name",
  "email": "newemail@example.com"
}
```

#### Change Password
```
POST /auth/change-password
Content-Type: application/json

{
  "old_password": "current_password",
  "new_password": "new_password"
}
```

### Visitor Tracking

#### Track Visit
```
POST /track/visit
Content-Type: application/json

{
  "page_url": "https://example.com/page",
  "referrer": "https://google.com"
}
```

**Response:**
```json
{
  "success": true,
  "visitor_id": 123,
  "message": "Visit tracked successfully"
}
```

#### Update Duration
```
POST /track/duration
Content-Type: application/json

{
  "page_visit_id": 456,
  "duration": 45,
  "scroll_depth": 75
}
```

### Statistics

#### Get Visitor Statistics
```
GET /stats/summary
```

#### Get All Visitors
```
GET /stats/visitors?limit=50&page=1
```

#### Get Active Visitors
```
GET /stats/active?minutes=30
```

#### Get Visitor Page History
```
GET /stats/history/{visitor_id}?limit=50
```

### Analytics

#### Get Summary
```
GET /analytics/summary?start_date=2024-01-01&end_date=2024-01-31
```

#### Get Today's Analytics
```
GET /analytics/today
```

#### Get Top Pages
```
GET /analytics/top-pages?limit=10&start_date=2024-01-01&end_date=2024-01-31
```

#### Get Top Referrers
```
GET /analytics/top-referrers?limit=10&start_date=2024-01-01&end_date=2024-01-31
```

#### Get Browser Statistics
```
GET /analytics/browsers?start_date=2024-01-01&end_date=2024-01-31
```

#### Get Device Statistics
```
GET /analytics/devices?start_date=2024-01-01&end_date=2024-01-31
```

#### Get OS Statistics
```
GET /analytics/os?start_date=2024-01-01&end_date=2024-01-31
```

#### Get Hourly Analytics
```
GET /analytics/hourly?date=2024-01-15
```

#### Get Bounce Rate
```
GET /analytics/bounce-rate?start_date=2024-01-01&end_date=2024-01-31
```

#### Generate Full Report
```
GET /analytics/report?start_date=2024-01-01&end_date=2024-01-31
```

## JavaScript Tracking

Add this script to your website to track visitors:

```html
<script src="/web-visitor/tracker.js"></script>
```

The tracker will automatically:
- Track page visits
- Monitor scroll depth
- Measure time on page
- Send data to the server

## Rate Limiting

No rate limiting is currently enforced. Implement based on your needs.

## Error Codes

- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

## Example Usage

### JavaScript
```javascript
// Track a visit
fetch('/web-visitor/api/track/visit', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({
    page_url: window.location.href,
    referrer: document.referrer
  })
});

// Get analytics
fetch('/web-visitor/api/analytics/summary?start_date=2024-01-01')
  .then(r => r.json())
  .then(data => console.log(data));
```

### PHP
```php
$ch = curl_init('/web-visitor/api/track/visit');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'page_url' => $_SERVER['REQUEST_URI'],
  'referrer' => $_SERVER['HTTP_REFERER'] ?? null
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_exec($ch);
```

## Support

For issues and questions, refer to the main README.md or create an issue on GitHub.
