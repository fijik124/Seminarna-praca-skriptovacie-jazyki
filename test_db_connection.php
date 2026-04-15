<?php
/**
 * Database Connection Diagnostics
 * Run this in your browser or terminal to diagnose connection issues:
 * http://localhost/path/to/test_db_connection.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$db_host = '127.0.0.1';
$db_name = 'skola';
$db_user = 'server';
$db_pass = 'server124Pas';

echo "<h1>Database Connection Test</h1>";
echo "<pre>";
echo "Host: $db_host\n";
echo "Database: $db_name\n";
echo "User: $db_user\n";
echo "Password: " . (strlen($db_pass) > 0 ? '[set]' : '[empty]') . "\n\n";

// Test 1: Check if PDO driver is available
echo "=== Test 1: PDO Drivers ===\n";
$drivers = PDO::getAvailableDrivers();
if (in_array('mysql', $drivers)) {
    echo "✓ MySQL PDO driver is available\n";
} else {
    echo "✗ MySQL PDO driver is NOT available\n";
    echo "Available drivers: " . implode(', ', $drivers) . "\n";
}

// Test 2: Try direct connection
echo "\n=== Test 2: Direct PDO Connection ===\n";
$dsn = "mysql:host=$db_host;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✓ Connection to MySQL server succeeded\n";
    echo "  Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    // Test 3: Check if database exists
    echo "\n=== Test 3: Database Existence ===\n";
    try {
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
        $exists = $stmt->fetch();
        if ($exists) {
            echo "✓ Database '$db_name' exists\n";
        } else {
            echo "✗ Database '$db_name' does NOT exist\n";
            echo "  Attempting to create it...\n";
            try {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo "✓ Database created successfully\n";
            } catch (PDOException $e) {
                echo "✗ Failed to create database: " . $e->getMessage() . "\n";
            }
        }
    } catch (PDOException $e) {
        echo "✗ Failed to query databases: " . $e->getMessage() . "\n";
    }
    
    // Test 4: Try connecting with database selected
    echo "\n=== Test 4: Connection with Database Selected ===\n";
    $dsn_with_db = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    try {
        $pdo_with_db = new PDO($dsn_with_db, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "✓ Connection to '$db_name' database succeeded\n";
        
        // Test 5: Check tables
        echo "\n=== Test 5: Database Tables ===\n";
        $stmt = $pdo_with_db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($tables)) {
            echo "✓ Found " . count($tables) . " table(s): " . implode(', ', $tables) . "\n";
        } else {
            echo "⚠ No tables found in database\n";
            echo "  You may need to run sql/schema.sql to initialize the database\n";
        }
    } catch (PDOException $e) {
        echo "✗ Connection with database failed: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Connection to MySQL server FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "\nCommon causes:\n";
    echo "  1. MySQL server is not running\n";
    echo "  2. Wrong host (should be 127.0.0.1 for localhost)\n";
    echo "  3. Wrong username or password\n";
    echo "  4. Firewall blocking MySQL port (3306)\n";
}

echo "\n=== Summary ===\n";
echo "If all tests pass, check sql/schema.sql to initialize your database.\n";
echo "If tests fail, use the error messages above to fix the configuration.\n";
echo "</pre>";
?>

