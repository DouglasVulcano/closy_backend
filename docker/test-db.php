#!/usr/bin/env php
<?php

/**
 * Database connection test script
 * Used during container startup to verify database connectivity
 */

try {
    // Get database configuration from environment variables
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'laravel';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    // Create PDO connection
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5, // 5 second timeout
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

    // Test the connection with a simple query
    $stmt = $pdo->query('SELECT 1');
    $result = $stmt->fetch();

    if ($pdo->query('SELECT 1')->fetchColumn() == 1) {
        echo "Database connection successful!\n";
        exit(0);
    }
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
    exit(1);
}
