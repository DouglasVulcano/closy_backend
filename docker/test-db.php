#!/usr/bin/env php
<?php

/**
 * Database connection test script
 * Used during container startup to verify database connectivity
 */

// Debug information
echo "=== Database Connection Test ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'NOT LOADED') . "\n";

try {
    // Get database configuration from environment variables
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? 'laravel';
    $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '';

    echo "Connection Parameters:\n";
    echo "  Host: {$host}\n";
    echo "  Port: {$port}\n";
    echo "  Database: {$database}\n";
    echo "  Username: {$username}\n";
    echo "  Password: " . (empty($password) ? 'EMPTY' : '[HIDDEN]') . "\n";

    // First try to connect without specifying database
    echo "\nTesting basic connection (without database)...\n";
    $basicDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10, // Increased timeout
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    $basicPdo = new PDO($basicDsn, $username, $password, $options);
    echo "Basic connection successful!\n";

    // Check if database exists
    echo "\nChecking if database '{$database}' exists...\n";
    $stmt = $basicPdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->execute([$database]);
    $dbExists = $stmt->fetch();

    if (!$dbExists) {
        echo "Database '{$database}' does not exist. Creating it...\n";
        $basicPdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database '{$database}' created successfully!\n";
    } else {
        echo "Database '{$database}' exists.\n";
    }

    // Now test connection with the specific database
    echo "\nTesting connection with database '{$database}'...\n";
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);

    // Test the connection with a simple query
    $result = $pdo->query('SELECT 1 as test')->fetchColumn();

    if ($result == 1) {
        echo "Database connection successful!\n";
        echo "MySQL Version: " . $pdo->query('SELECT VERSION()')->fetchColumn() . "\n";
        exit(0);
    }
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    
    // Additional debugging for common issues
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "\nDEBUG: Connection refused - Database server may not be running or accessible\n";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "\nDEBUG: Access denied - Check username/password credentials\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "\nDEBUG: Unknown database - Database may not exist\n";
    }
    
    exit(1);
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
    exit(1);
}
