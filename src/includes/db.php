<?php
// src/includes/db.php

// Define the path to the SQLite database
$dbPath = __DIR__ . '/../../database/jules.db';

// Ensure the directory exists
$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0777, true);
}

try {
    // Connect to SQLite
    $pdo = new PDO("sqlite:" . $dbPath);

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Enable foreign keys
    $pdo->exec("PRAGMA foreign_keys = ON;");

} catch (PDOException $e) {
    // Service Unavailable
    http_response_code(503);
    echo "<div style='font-family: sans-serif; padding: 20px; text-align: center;'>";
    echo "<h1>System Error</h1>";
    echo "<p>Unable to connect to the database.</p>";
    echo "<small>" . htmlspecialchars($e->getMessage()) . "</small>";
    echo "</div>";
    exit;
}
?>
