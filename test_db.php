<?php
// test_db.php
// Run this file in your browser or CLI to test the database connection.
// e.g., http://yourdomain.com/test_db.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

$configFile = __DIR__ . '/src/includes/config.php';

if (file_exists($configFile)) {
    echo "<p style='color: green;'>Found configuration file at: " . htmlspecialchars($configFile) . "</p>";
    require_once $configFile;
    echo "<ul>";
    echo "<li>Host: " . htmlspecialchars($db_host ?? 'Not Set') . "</li>";
    echo "<li>Database: " . htmlspecialchars($db_name ?? 'Not Set') . "</li>";
    echo "<li>User: " . htmlspecialchars($db_user ?? 'Not Set') . "</li>";
    echo "<li>Password: [HIDDEN]</li>";
    echo "</ul>";
} else {
    echo "<p style='color: orange;'>No 'src/includes/config.php' found. Using defaults/env vars.</p>";
}

echo "<h2>Attempting Connection...</h2>";

try {
    // Manually duplicating the logic from db.php to verify it in isolation
    $host = $db_host ?? getenv('DB_HOST') ?: '127.0.0.1';
    $db   = $db_name ?? getenv('DB_NAME') ?: 'jules_db';
    $user = $db_user ?? getenv('DB_USER') ?: 'jules';
    $pass = $db_pass ?? getenv('DB_PASS') ?: 'password';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

    echo "<h3 style='color: green;'>SUCCESS: Connected to database '" . htmlspecialchars($db) . "'</h3>";

    // Test a query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "<p>MySQL Version: " . htmlspecialchars($version) . "</p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>FAILURE: Could not connect.</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Please check your credentials in <code>src/includes/config.php</code>.</p>";
}
?>
