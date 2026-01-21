<?php
// src/includes/db.php

// 1. Try to load local configuration if it exists
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// 2. Fallback to Environment Variables or Defaults
// Note: We use the '??' operator to check if variables were set by config.php
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

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If we are in a production environment (which we assume if display_errors is off),
    // we should output a JSON error or a clean message instead of a full stack trace or 500.

    // Check if we are likely in an API request (optional, but good for AJAX)
    $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;

    if ($isApi) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed.']);
    } else {
        // User facing error
        http_response_code(500);
        echo "<div style='padding: 20px; font-family: sans-serif; text-align: center; color: #333;'>";
        echo "<h1>System Error</h1>";
        echo "<p>Unable to connect to the database.</p>";
        // Only show detailed error if debug mode is implied or for admins (simplified here)
        // echo "<small>" . htmlspecialchars($e->getMessage()) . "</small>";
        echo "<p>Please ensure your <code>src/includes/config.php</code> is set up correctly.</p>";
        echo "</div>";
    }

    // Stop execution
    exit;
}
?>
