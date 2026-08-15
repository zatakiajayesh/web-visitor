<?php
/**
 * Database Setup Script
 * Creates database and tables
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/helpers.php';

// Load environment
loadEnv(__DIR__ . '/../.env');

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME');

echo "Web Visitor Tracker - Database Setup\n";
echo "=====================================\n\n";

// Connect to MySQL without database
try {
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "[1/3] Creating database...\n";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Database created successfully\n\n";
    } else {
        die("✗ Error creating database: " . $conn->error);
    }
    
    // Select database
    $conn->select_db($dbName);
    
    echo "[2/3] Creating tables...\n";
    
    // Read schema file
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        die("Schema file not found: $schemaFile");
    }
    
    $schema = file_get_contents($schemaFile);
    
    // Execute schema
    $queries = array_filter(array_map('trim', explode(';', $schema)));
    $count = 0;
    
    foreach ($queries as $query) {
        if (!empty($query) && strpos($query, '--') !== 0) {
            if ($conn->query($query) === TRUE) {
                $count++;
            } else {
                echo "Warning: " . $conn->error . "\n";
            }
        }
    }
    
    echo "✓ $count tables/views created successfully\n\n";
    
    echo "[3/3] Verifying setup...\n";
    
    // Check tables
    $result = $conn->query("SHOW TABLES");
    $tableCount = $result->num_rows;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    $userCount = $row['count'];
    
    echo "✓ Database verified\n";
    echo "  - Tables: $tableCount\n";
    echo "  - Users: $userCount\n\n";
    
    echo "Setup Complete!\n";
    echo "=====================================\n\n";
    echo "Admin Credentials:\n";
    echo "  Email: admin@example.com\n";
    echo "  Password: admin123\n\n";
    echo "Access the admin panel at: /web-visitor/admin\n";
    
    $conn->close();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
